<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Receipt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'claim_id',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'mime_type',
        'file_hash',
        'uploaded_by',
        'is_primary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Get the claim associated with the receipt.
     */
    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    /**
     * Get the user who uploaded the receipt.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Validate file specifications against business rules.
     */
    public static function validateFileSpecs(\Illuminate\Http\UploadedFile $file): array
    {
        $errors = [];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedMimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/pdf',
        ];

        // Check file size
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size cannot exceed 5MB.';
        }

        // Check file type
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'Only JPEG, PNG, and PDF files are allowed.';
        }

        // Check for potential malicious files
        if (!$file->isValid()) {
            $errors[] = 'File upload failed or file is corrupted.';
        }

        return $errors;
    }

    /**
     * Store uploaded file with security checks.
     */
    public static function storeFile(\Illuminate\Http\UploadedFile $file, int $claimId, int $uploadedBy): self
    {
        // Validate file specifications
        $validationErrors = self::validateFileSpecs($file);
        if (!empty($validationErrors)) {
            throw new \Exception(implode(' ', $validationErrors));
        }

        try {
            // Generate secure file path
            $filePath = self::generateFilePath($claimId);
            $storedFilename = self::generateStoredFilename($file);
            $fullPath = $filePath . '/' . $storedFilename;

            // Calculate file hash for duplicate detection
            $fileHash = hash_file('sha256', $file->getPathname());

            // Check for duplicate files within the same claim
            $existingReceipt = static::where('claim_id', $claimId)
                ->where('file_hash', $fileHash)
                ->first();

            if ($existingReceipt) {
                throw new \Exception('This file has already been uploaded for this claim.');
            }

            // Store file securely
            $storedPath = $file->storeAs($filePath, $storedFilename, 'receipts');

            if (!$storedPath) {
                throw new \Exception('Failed to store file.');
            }

            // Create receipt record
            $receipt = static::create([
                'claim_id' => $claimId,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $storedPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'file_hash' => $fileHash,
                'uploaded_by' => $uploadedBy,
                'is_primary' => false, // Will be set later if needed
            ]);

            Log::info('Receipt stored successfully', [
                'receipt_id' => $receipt->id,
                'claim_id' => $claimId,
                'filename' => $file->getClientOriginalName(),
                'uploaded_by' => $uploadedBy
            ]);

            return $receipt;

        } catch (\Exception $e) {
            // Clean up stored file if database record creation failed
            if (isset($storedPath)) {
                Storage::disk('receipts')->delete($storedPath);
            }

            Log::error('Receipt storage failed: ' . $e->getMessage(), [
                'claim_id' => $claimId,
                'filename' => $file->getClientOriginalName(),
                'uploaded_by' => $uploadedBy
            ]);

            throw $e;
        }
    }

    /**
     * Delete receipt file and record.
     */
    public function deleteFile(): bool
    {
        try {
            // Delete physical file
            if (Storage::disk('receipts')->exists($this->file_path)) {
                Storage::disk('receipts')->delete($this->file_path);
            }

            // Delete database record
            return $this->delete();

        } catch (\Exception $e) {
            Log::error('Receipt deletion failed: ' . $e->getMessage(), [
                'receipt_id' => $this->id,
                'file_path' => $this->file_path
            ]);

            return false;
        }
    }

    /**
     * Generate organized file path for storage.
     */
    private static function generateFilePath(int $claimId): string
    {
        $year = date('Y');
        $month = date('m');

        return "receipts/{$year}/{$month}/claim_{$claimId}";
    }

    /**
     * Generate secure stored filename.
     */
    private static function generateStoredFilename(\Illuminate\Http\UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = time();
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get full URL for file download.
     */
    public function getDownloadUrl(): string
    {
        $routeName = 'employee.receipts.download'; // Default to employee route

        if (Auth::check()) {
            try {
                if (Auth::user()->isFinanceAdmin()) {
                    $routeName = 'admin.receipts.download';
                }
                // Default to employee route for all other cases
            } catch (\Exception $e) {
                // Fallback to employee route if role checking fails
                Log::warning('Could not determine user role for receipt download URL, defaulting to employee route', [
                    'receipt_id' => $this->id,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        return route($routeName, $this->id);
    }

    /**
     * Get full URL for file preview.
     */
    public function getPreviewUrl(): string
    {
        $routeName = 'employee.receipts.preview'; // Default to employee route

        if (Auth::check()) {
            try {
                if (Auth::user()->isFinanceAdmin()) {
                    $routeName = 'admin.receipts.preview';
                }
                // Default to employee route for all other cases
            } catch (\Exception $e) {
                // Fallback to employee route if role checking fails
                Log::warning('Could not determine user role for receipt preview URL, defaulting to employee route', [
                    'receipt_id' => $this->id,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        return route($routeName, $this->id);
    }

    /**
     * Check if file can be previewed in browser.
     */
    public function canBePreviewed(): bool
    {
        return in_array($this->mime_type, [
            'image/jpeg',
            'image/jpg',
            'image/png',
        ]);
    }

    /**
     * Get formatted file size for display.
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file extension for display.
     */
    public function getFileExtension(): string
    {
        return strtoupper(pathinfo($this->original_filename, PATHINFO_EXTENSION));
    }

    /**
     * Set as primary receipt for the claim.
     */
    public function setAsPrimary(): bool
    {
        try {
            DB::transaction(function () {
                // Remove primary status from other receipts of the same claim
                static::where('claim_id', $this->claim_id)
                    ->where('id', '!=', $this->id)
                    ->update(['is_primary' => false]);

                // Set this receipt as primary
                $this->update(['is_primary' => true]);
            });

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to set primary receipt: ' . $e->getMessage(), [
                'receipt_id' => $this->id,
                'claim_id' => $this->claim_id
            ]);

            return false;
        }
    }

    /**
     * Scope to get primary receipts only.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to get receipts by uploader.
     */
    public function scopeByUploader($query, $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    /**
     * Scope to get receipts by file type.
     */
    public function scopeByFileType($query, $mimeType)
    {
        return $query->where('mime_type', $mimeType);
    }
}
