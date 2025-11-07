<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Receipt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReceiptUploadService
{
    /**
     * Upload multiple receipts for a claim with validation and business rules.
     */
    public function uploadMultipleReceipts(array $files, int $claimId, int $uploadedBy): array
    {
        $results = [
            'success' => true,
            'uploaded' => [],
            'failed' => [],
            'errors' => [],
        ];

        // Validate claim exists and user has permission
        $claim = Claim::findOrFail($claimId);
        if (!$this->canUserUploadReceipt($uploadedBy, $claim)) {
            return [
                'success' => false,
                'message' => 'You do not have permission to upload receipts for this claim.',
            ];
        }

        // Check if claim allows receipt uploads
        if (!$this->canClaimAcceptReceipts($claim)) {
            return [
                'success' => false,
                'message' => 'This claim cannot accept new receipts in its current status.',
            ];
        }

        try {
            DB::beginTransaction();

            foreach ($files as $index => $file) {
                if (!$file instanceof UploadedFile) {
                    $results['failed'][] = [
                        'index' => $index,
                        'filename' => 'unknown',
                        'error' => 'Invalid file format.',
                    ];
                    continue;
                }

                try {
                    $receipt = Receipt::storeFile($file, $claimId, $uploadedBy);
                    $results['uploaded'][] = [
                        'receipt_id' => $receipt->id,
                        'filename' => $receipt->original_filename,
                        'size' => $receipt->getFormattedFileSize(),
                    ];

                    // Set first receipt as primary if no primary exists
                    if ($claim->receipts()->primary()->count() === 0) {
                        $receipt->setAsPrimary();
                    }

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'index' => $index,
                        'filename' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Validate claim against receipt totals after upload
            $this->validateClaimReceiptTotals($claim);

            DB::commit();

            // Log successful upload operation
            Log::info('Multiple receipts uploaded successfully', [
                'claim_id' => $claimId,
                'uploaded_by' => $uploadedBy,
                'total_files' => count($files),
                'successful_uploads' => count($results['uploaded']),
                'failed_uploads' => count($results['failed']),
            ]);

            return $results;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Multiple receipt upload failed: ' . $e->getMessage(), [
                'claim_id' => $claimId,
                'uploaded_by' => $uploadedBy,
                'total_files' => count($files),
            ]);

            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Replace an existing receipt with a new file.
     */
    public function replaceReceipt(UploadedFile $newFile, int $receiptId, int $uploadedBy): array
    {
        try {
            DB::beginTransaction();

            $oldReceipt = Receipt::findOrFail($receiptId);
            $claim = $oldReceipt->claim;

            // Verify user permissions
            if (!$this->canUserUploadReceipt($uploadedBy, $claim)) {
                throw new \Exception('You do not have permission to replace receipts for this claim.');
            }

            // Check if claim allows receipt modifications
            if (!$this->canClaimAcceptReceipts($claim)) {
                throw new \Exception('This claim cannot accept receipt changes in its current status.');
            }

            // Store the old receipt info for logging
            $oldFilename = $oldReceipt->original_filename;
            $wasPrimary = $oldReceipt->is_primary;

            // Delete old receipt (both file and record)
            $oldReceipt->deleteFile();

            // Upload new receipt
            $newReceipt = Receipt::storeFile($newFile, $claim->id, $uploadedBy);

            // Set as primary if the old one was primary
            if ($wasPrimary) {
                $newReceipt->setAsPrimary();
            }

            DB::commit();

            Log::info('Receipt replaced successfully', [
                'claim_id' => $claim->id,
                'receipt_id' => $newReceipt->id,
                'uploaded_by' => $uploadedBy,
                'old_filename' => $oldFilename,
                'new_filename' => $newReceipt->original_filename,
            ]);

            return [
                'success' => true,
                'receipt' => $newReceipt,
                'message' => 'Receipt replaced successfully.',
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Receipt replacement failed: ' . $e->getMessage(), [
                'receipt_id' => $receiptId,
                'uploaded_by' => $uploadedBy,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to replace receipt: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a receipt with proper validation and cleanup.
     */
    public function deleteReceipt(int $receiptId, int $deletedBy): array
    {
        try {
            $receipt = Receipt::findOrFail($receiptId);
            $claim = $receipt->claim;

            // Verify user permissions
            if (!$this->canUserDeleteReceipt($deletedBy, $claim)) {
                throw new \Exception('You do not have permission to delete receipts for this claim.');
            }

            // Check if claim allows receipt deletion
            if (!$this->canClaimAcceptReceipts($claim)) {
                throw new \Exception('This claim cannot accept receipt changes in its current status.');
            }

            // Prevent deletion if it's the only receipt
            if ($claim->receipts()->count() === 1) {
                throw new \Exception('Cannot delete the only receipt for a claim.');
            }

            // Check if it's the primary receipt
            $wasPrimary = $receipt->is_primary;
            $filename = $receipt->original_filename;

            // Delete the receipt
            $success = $receipt->deleteFile();

            if (!$success) {
                throw new \Exception('Failed to delete receipt file.');
            }

            // If deleted receipt was primary, set another one as primary
            if ($wasPrimary) {
                $newPrimary = $claim->receipts()->first();
                if ($newPrimary) {
                    $newPrimary->setAsPrimary();
                }
            }

            Log::info('Receipt deleted successfully', [
                'claim_id' => $claim->id,
                'receipt_id' => $receiptId,
                'deleted_by' => $deletedBy,
                'filename' => $filename,
                'was_primary' => $wasPrimary,
            ]);

            return [
                'success' => true,
                'message' => 'Receipt deleted successfully.',
            ];

        } catch (\Exception $e) {
            Log::error('Receipt deletion failed: ' . $e->getMessage(), [
                'receipt_id' => $receiptId,
                'deleted_by' => $deletedBy,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete receipt: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Set a specific receipt as the primary receipt for a claim.
     */
    public function setPrimaryReceipt(int $receiptId, int $userId): array
    {
        try {
            $receipt = Receipt::findOrFail($receiptId);
            $claim = $receipt->claim;

            // Verify user permissions
            if (!$this->canUserUploadReceipt($userId, $claim)) {
                throw new \Exception('You do not have permission to modify receipts for this claim.');
            }

            // Check if claim allows receipt modifications
            if (!$this->canClaimAcceptReceipts($claim)) {
                throw new \Exception('This claim cannot accept receipt changes in its current status.');
            }

            $success = $receipt->setAsPrimary();

            if (!$success) {
                throw new \Exception('Failed to set primary receipt.');
            }

            Log::info('Primary receipt updated', [
                'claim_id' => $claim->id,
                'receipt_id' => $receiptId,
                'user_id' => $userId,
            ]);

            return [
                'success' => true,
                'message' => 'Primary receipt set successfully.',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to set primary receipt: ' . $e->getMessage(), [
                'receipt_id' => $receiptId,
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to set primary receipt: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get storage statistics for receipts.
     */
    public function getStorageStatistics(): array
    {
        try {
            $disk = Storage::disk('receipts');
            $totalReceipts = Receipt::count();
            $totalSize = Receipt::sum('file_size');
            $totalFiles = 0;
            $diskSize = 0;

            // Calculate disk usage (might be slow on large storages)
            $directories = ['receipts'];
            foreach ($directories as $directory) {
                if ($disk->exists($directory)) {
                    $files = $disk->allFiles($directory);
                    $totalFiles += count($files);
                    foreach ($files as $file) {
                        $diskSize += $disk->size($file);
                    }
                }
            }

            return [
                'total_receipts' => $totalReceipts,
                'total_database_size' => $this->formatBytes($totalSize),
                'total_disk_files' => $totalFiles,
                'total_disk_size' => $this->formatBytes($diskSize),
                'average_receipt_size' => $totalReceipts > 0 ? $this->formatBytes($totalSize / $totalReceipts) : '0 B',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get storage statistics: ' . $e->getMessage());

            return [
                'error' => 'Failed to retrieve storage statistics.',
            ];
        }
    }

    /**
     * Check if user can upload receipts for a claim.
     */
    private function canUserUploadReceipt(int $userId, Claim $claim): bool
    {
        // User can upload if they own the claim
        if ($claim->user_id === $userId) {
            return true;
        }

        // Admin users can upload to any claim
        $user = \App\Models\User::find($userId);
        if ($user && $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can delete receipts for a claim.
     */
    private function canUserDeleteReceipt(int $userId, Claim $claim): bool
    {
        // Same permissions as upload for now
        return $this->canUserUploadReceipt($userId, $claim);
    }

    /**
     * Check if claim can accept receipt changes based on status.
     */
    private function canClaimAcceptReceipts(Claim $claim): bool
    {
        $allowedStatuses = ['draft', 'returned_for_info'];
        return in_array($claim->status, $allowedStatuses);
    }

    /**
     * Validate claim amount against receipt totals.
     */
    private function validateClaimReceiptTotals(Claim $claim): void
    {
        // This is a business validation that could be enhanced
        // For now, we'll just log if there are any discrepancies

        if ($claim->amount <= 0) {
            Log::warning('Claim has zero or negative amount', [
                'claim_id' => $claim->id,
                'amount' => $claim->amount,
            ]);
        }

        $receiptCount = $claim->receipts()->count();
        if ($receiptCount === 0) {
            Log::warning('Claim has no receipts', [
                'claim_id' => $claim->id,
            ]);
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}