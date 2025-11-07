<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    /**
     * Download a receipt file with proper authorization checks.
     */
    public function download(Receipt $receipt)
    {
        try {
            // Verify user has permission to access this receipt
            if (!$this->canUserAccessReceipt($receipt)) {
                abort(403, 'You do not have permission to access this receipt.');
            }

            // Check if file exists
            if (!Storage::disk('receipts')->exists($receipt->file_path)) {
                abort(404, 'Receipt file not found.');
            }

            // Get the file contents
            $fileContents = Storage::disk('receipts')->get($receipt->file_path);

            if ($fileContents === false) {
                abort(500, 'Failed to read receipt file.');
            }

            // Log the download for audit purposes
            Log::info('Receipt downloaded', [
                'receipt_id' => $receipt->id,
                'claim_id' => $receipt->claim_id,
                'user_id' => Auth::id(),
                'filename' => $receipt->original_filename,
                'ip_address' => request()->ip(),
            ]);

            // Return the file as a download response
            return response($fileContents)
                ->header('Content-Type', $receipt->mime_type)
                ->header('Content-Disposition', 'attachment; filename="' . $receipt->original_filename . '"')
                ->header('Content-Length', strlen($fileContents))
                ->header('Cache-Control', 'private, no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('Receipt download failed: ' . $e->getMessage(), [
                'receipt_id' => $receipt->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            if ($e->getCode() === 403 || $e->getCode() === 404) {
                throw $e;
            }

            abort(500, 'Failed to download receipt.');
        }
    }

    /**
     * Preview a receipt file in the browser (for image files only).
     */
    public function preview(Receipt $receipt)
    {
        try {
            // Verify user has permission to access this receipt
            if (!$this->canUserAccessReceipt($receipt)) {
                abort(403, 'You do not have permission to access this receipt.');
            }

            // Check if file can be previewed
            if (!$receipt->canBePreviewed()) {
                abort(400, 'This file type cannot be previewed. Please download the file instead.');
            }

            // Check if file exists
            if (!Storage::disk('receipts')->exists($receipt->file_path)) {
                abort(404, 'Receipt file not found.');
            }

            // Get the file contents
            $fileContents = Storage::disk('receipts')->get($receipt->file_path);

            if ($fileContents === false) {
                abort(500, 'Failed to read receipt file.');
            }

            // Log the preview for audit purposes
            Log::info('Receipt previewed', [
                'receipt_id' => $receipt->id,
                'claim_id' => $receipt->claim_id,
                'user_id' => Auth::id(),
                'filename' => $receipt->original_filename,
                'ip_address' => request()->ip(),
            ]);

            // Return the file for inline display
            return response($fileContents)
                ->header('Content-Type', $receipt->mime_type)
                ->header('Content-Disposition', 'inline; filename="' . $receipt->original_filename . '"')
                ->header('Content-Length', strlen($fileContents))
                ->header('Cache-Control', 'private, max-age=3600') // Cache for 1 hour for previews
                ->header('X-Content-Type-Options', 'nosniff'); // Prevent MIME type sniffing

        } catch (\Exception $e) {
            Log::error('Receipt preview failed: ' . $e->getMessage(), [
                'receipt_id' => $receipt->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            if ($e->getCode() === 403 || $e->getCode() === 404 || $e->getCode() === 400) {
                throw $e;
            }

            abort(500, 'Failed to preview receipt.');
        }
    }

    /**
     * Get receipt metadata (for AJAX calls or API responses).
     */
    public function getReceiptInfo(Receipt $receipt)
    {
        try {
            // Verify user has permission to access this receipt
            if (!$this->canUserAccessReceipt($receipt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access this receipt.',
                ], 403);
            }

            // Return receipt information
            return response()->json([
                'success' => true,
                'receipt' => [
                    'id' => $receipt->id,
                    'original_filename' => $receipt->original_filename,
                    'file_size' => $receipt->getFormattedFileSize(),
                    'mime_type' => $receipt->mime_type,
                    'file_extension' => $receipt->getFileExtension(),
                    'can_be_previewed' => $receipt->canBePreviewed(),
                    'is_primary' => $receipt->is_primary,
                    'uploaded_by' => $receipt->uploader->name ?? 'Unknown',
                    'upload_date' => $receipt->created_at->format('M d, Y H:i'),
                    'download_url' => $receipt->getDownloadUrl(),
                    'preview_url' => $receipt->canBePreviewed() ? $receipt->getPreviewUrl() : null,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get receipt info: ' . $e->getMessage(), [
                'receipt_id' => $receipt->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve receipt information.',
            ], 500);
        }
    }

    /**
     * Set a receipt as primary for its claim.
     */
    public function setPrimary(Request $request, Receipt $receipt)
    {
        try {
            // Verify user has permission to modify this receipt
            if (!$this->canUserModifyReceipt($receipt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to modify this receipt.',
                ], 403);
            }

            // Validate request
            $request->validate([
                'confirm' => ['required', 'boolean', 'accepted'],
            ]);

            // Set as primary
            $success = $receipt->setAsPrimary();

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to set receipt as primary.',
                ], 500);
            }

            Log::info('Receipt set as primary', [
                'receipt_id' => $receipt->id,
                'claim_id' => $receipt->claim_id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Receipt set as primary successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to set primary receipt: ' . $e->getMessage(), [
                'receipt_id' => $receipt->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set receipt as primary.',
            ], 500);
        }
    }

    /**
     * Delete a receipt with proper authorization.
     */
    public function destroy(Receipt $receipt)
    {
        try {
            // Verify user has permission to delete this receipt
            if (!$this->canUserModifyReceipt($receipt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this receipt.',
                ], 403);
            }

            // Use the ReceiptUploadService for proper deletion
            $service = new \App\Services\ReceiptUploadService();
            $result = $service->deleteReceipt($receipt->id, Auth::id());

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            Log::info('Receipt deleted via controller', [
                'receipt_id' => $receipt->id,
                'claim_id' => $receipt->claim_id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete receipt: ' . $e->getMessage(), [
                'receipt_id' => $receipt->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete receipt.',
            ], 500);
        }
    }

    /**
     * Check if the current user can access the receipt.
     */
    private function canUserAccessReceipt(Receipt $receipt): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // User can access if they own the claim
        if ($receipt->claim->user_id === $user->id) {
            return true;
        }

        // Admin users can access any receipt
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Check if the current user can modify the receipt.
     */
    private function canUserModifyReceipt(Receipt $receipt): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Only allow modifications if claim is in draft or returned_for_info status
        $allowedStatuses = ['draft', 'returned_for_info'];
        if (!in_array($receipt->claim->status, $allowedStatuses)) {
            return false;
        }

        // User can modify if they own the claim
        if ($receipt->claim->user_id === $user->id) {
            return true;
        }

        // Admin users can modify any receipt
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }
}