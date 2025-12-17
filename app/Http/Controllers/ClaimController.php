<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClaimRequest;
use App\Http\Requests\UpdateClaimRequest;
use App\Http\Requests\UploadReceiptRequest;
use App\Models\Claim;
use App\Models\ClaimCategory;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClaimController extends Controller
{
    // Employee methods
    public function index(Request $request)
    {
        $user = Auth::user();

        // Build query with filters
        $claims = Claim::where('user_id', $user->id)
            ->with(['category', 'receipts', 'approvals'])
            ->when($request->status, function ($query, $status) {
                $query->byStatus($status);
            })
            ->when($request->boolean('unread'), function ($query) {
                $query->where('is_read', false);
            })
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('claim_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('claim_date', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get statistics for dashboard
        $statistics = Claim::getClaimStatistics($user->id);

        // Get available categories for filter (only active ones)
        $categories = ClaimCategory::active()->orderBy('display_name')->get();

        // Unread count for this employee
        $unreadCount = Claim::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('employee.claims.index', compact('claims', 'statistics', 'categories', 'unreadCount'));
    }

    public function create()
    {
        // Get available claim categories (only active ones)
        // Focus on the new 10-category structure (IDs 19-28)
        $categories = ClaimCategory::active()
            ->whereIn('id', [19, 20, 21, 22, 23, 24, 25, 26, 27, 28])
            ->orderBy('display_name')
            ->get();

        // Set default currency
        $defaultCurrency = 'MYR';

        return view('employee.claims.create', compact('categories', 'defaultCurrency'));
    }

    public function store(StoreClaimRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Create the claim
            $claim = Claim::create([
                'user_id' => $user->id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'currency' => 'MYR', // Default to Malaysian Ringgit
                'claim_date' => $request->claim_date,
                'priority' => $request->priority ?? 'normal',
                'status' => 'draft',
            ]);

            // Handle category-specific fields dynamically
            $categoryData = $this->handleCategorySpecificFields($request);

            // Store category-specific data in a JSON field or separate table
            if (!empty($categoryData)) {
                $claim->update(['category_data' => json_encode($categoryData)]);
            }

            // Handle receipt uploads
            if ($request->hasFile('receipts')) {
                foreach ($request->file('receipts') as $index => $receiptFile) {
                    $receipt = Receipt::storeFile($receiptFile, $claim->id, $user->id);

                    // Set first receipt as primary if no primary exists
                    if ($index === 0) {
                        $receipt->setAsPrimary();
                    }
                }
            }

            // Log the claim creation
            Log::info('Claim created successfully', [
                'claim_id' => $claim->id,
                'user_id' => $user->id,
                'amount' => $claim->amount,
                'category' => $claim->category->name ?? 'Unknown'
            ]);

            DB::commit();

            return redirect()
                ->route('employee.claims.show', $claim->id)
                ->with('success', 'Claim created successfully! Please review and submit for approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Claim creation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['receipts'])
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create claim. ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $claim = Claim::with(['user', 'category', 'receipts', 'approvals.approver', 'auditLogs.user'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Mark as read for the employee when opened
        if (!$claim->is_read) {
            $claim->update(['is_read' => true]);
        }

        // Decode category-specific data if exists
        $categoryData = [];
        if ($claim->category_data) {
            $categoryData = json_decode($claim->category_data, true);
        }

        return view('employee.claims.show', compact('claim', 'categoryData'));
    }

    /**
     * Printable view of a single claim for employees.
     */
    public function printEmployeeClaim($id)
    {
        $user = Auth::user();
        $claim = Claim::with(['user', 'category', 'receipts', 'approvals.approver', 'auditLogs.user'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return view('reports.claim_detail', [
            'claim' => $claim,
            'viewer' => 'employee',
        ]);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $claim = Claim::with(['category', 'receipts'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Check if claim can be edited
        if (!$claim->canBeEdited()) {
            return redirect()
                ->route('employee.claims.show', $claim->id)
                ->with('error', 'This claim cannot be edited because it has been submitted for approval.');
        }

        // Get available categories (only active ones)
        // Focus on the new 10-category structure (IDs 19-28)
        $categories = ClaimCategory::active()
            ->whereIn('id', [19, 20, 21, 22, 23, 24, 25, 26, 27, 28])
            ->orderBy('display_name')
            ->get();

        // Decode category-specific data if exists
        $categoryData = [];
        if ($claim->category_data) {
            $categoryData = json_decode($claim->category_data, true);
        }

        return view('employee.claims.edit', compact('claim', 'categories', 'categoryData'));
    }

    public function update(UpdateClaimRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $claim = Claim::where('user_id', $user->id)->findOrFail($id);

            // Check if claim can be edited
            if (!$claim->canBeEdited()) {
                return redirect()
                    ->route('employee.claims.show', $claim->id)
                    ->with('error', 'This claim cannot be edited because it has been submitted for approval.');
            }

            // Update claim data
            $claimData = $request->only([
                'title', 'description', 'amount', 'currency',
                'claim_date', 'category_id', 'priority'
            ]);
            $claimData['status'] = 'draft';
            $claimData['isadminread'] = false;

            $claim->update($claimData);

            // Handle category-specific fields dynamically
            $categoryData = $this->handleCategorySpecificFields($request);

            // Update category-specific data
            if (!empty($categoryData)) {
                $claim->update(['category_data' => json_encode($categoryData)]);
            } else {
                $claim->update(['category_data' => null]);
            }

            // Log the claim update
            Log::info('Claim updated successfully', [
                'claim_id' => $claim->id,
                'user_id' => $user->id,
                'updated_fields' => array_keys($claimData)
            ]);

            DB::commit();

            return redirect()
                ->route('employee.claims.show', $claim->id)
                ->with('success', 'Claim updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Claim update failed: ' . $e->getMessage(), [
                'claim_id' => $id,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update claim. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $claim = Claim::where('user_id', $user->id)->findOrFail($id);

            // Check if claim can be deleted (only draft claims)
            if (!$claim->canBeEdited()) {
                return redirect()
                    ->route('employee.claims.show', $claim->id)
                    ->with('error', 'This claim cannot be deleted because it has been submitted for approval.');
            }

            // Delete all receipts and their files
            foreach ($claim->receipts as $receipt) {
                $receipt->deleteFile();
            }

            // Delete the claim
            $claimId = $claim->id;
            $claimAmount = $claim->amount;
            $claim->delete();

            // Log the claim deletion
            Log::info('Claim deleted successfully', [
                'claim_id' => $claimId,
                'user_id' => $user->id,
                'amount' => $claimAmount
            ]);

            DB::commit();

            return redirect()
                ->route('employee.claims.index')
                ->with('success', 'Claim deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Claim deletion failed: ' . $e->getMessage(), [
                'claim_id' => $id,
                'user_id' => Auth::id()
            ]);

            return redirect()
                ->route('employee.claims.index')
                ->with('error', 'Failed to delete claim. ' . $e->getMessage());
        }
    }

    public function submit($id)
    {
        try {
            $user = Auth::user();
            $claim = Claim::where('user_id', $user->id)->findOrFail($id);

            // Use the model's submitClaim method which includes all business logic
            $claim->submitClaim();

            Log::info('Claim submitted for approval successfully', [
                'claim_id' => $claim->id,
                'user_id' => $user->id,
                'amount' => $claim->amount
            ]);

            return redirect()
                ->route('employee.claims.show', $claim->id)
                ->with('success', 'Claim submitted for approval! You will be notified once it has been reviewed.');

        } catch (\Exception $e) {
            Log::error('Claim submission failed: ' . $e->getMessage(), [
                'claim_id' => $id,
                'user_id' => Auth::id()
            ]);

            return redirect()
                ->route('employee.claims.show', $id)
                ->with('error', 'Failed to submit claim. ' . $e->getMessage());
        }
    }

    /**
     * Mark all employee claims as read for the authenticated user.
     */
    public function markAllReadEmployee()
    {
        $user = Auth::user();
        Claim::where('user_id', $user->id)->where('is_read', false)->update(['is_read' => true]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Export employee claims for a given month as CSV.
     */
    public function exportEmployeeClaims(Request $request)
    {
        $user = Auth::user();
        $monthInput = $request->input('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $claims = Claim::with(['category'])
            ->where('user_id', $user->id)
            ->whereBetween('claim_date', [$start, $end])
            ->orderBy('claim_date')
            ->get();

        $fileName = "claims-{$start->format('Y-m')}.csv";

        return response()->streamDownload(function () use ($claims) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Title', 'Category', 'Amount', 'Status', 'Claim Date']);
            foreach ($claims as $claim) {
                fputcsv($handle, [
                    $claim->id,
                    $claim->title,
                    $claim->category->display_name ?? $claim->category->name ?? 'N/A',
                    $claim->amount,
                    $claim->status,
                    optional($claim->claim_date)->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export employee claims for a given month as PDF.
     */
    public function exportEmployeeClaimsPdf(Request $request)
    {
        $user = Auth::user();
        $monthInput = $request->input('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $claims = Claim::with(['category'])
            ->where('user_id', $user->id)
            ->whereBetween('claim_date', [$start, $end])
            ->orderBy('claim_date')
            ->get();

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return redirect()->back()->with('error', 'PDF export not available (install barryvdh/laravel-dompdf).');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.claims_summary', [
            'claims' => $claims,
            'title' => 'Claims Summary',
            'period' => $start->format('F Y'),
        ]);

        return $pdf->download("claims-{$start->format('Y-m')}.pdf");
    }

    // Admin methods
    public function adminIndex(Request $request)
    {
        // Check if user is finance admin
        if (!Auth::user()->hasRole('finance_admin')) {
            abort(403, 'Unauthorized access.');
        }

        // Build query with advanced filters
        $claims = Claim::with(['user', 'category', 'receipts', 'approvals'])
            ->when($request->status, function ($query, $status) {
                $query->byStatus($status);
            })
            ->when($request->boolean('unread'), function ($query) {
                $query->where('is_admin_read', false);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('claim_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('claim_date', '<=', $dateTo);
            })
            ->when($request->amount_min, function ($query, $amountMin) {
                $query->where('amount', '>=', $amountMin);
            })
            ->when($request->amount_max, function ($query, $amountMax) {
                $query->where('amount', '<=', $amountMax);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get overall statistics for admin dashboard
        $statistics = Claim::getClaimStatistics(); // All users

        // Get available filters (only active ones)
        // Focus on the new 10-category structure (IDs 19-28)
        $categories = ClaimCategory::active()
            ->whereIn('id', [19, 20, 21, 22, 23, 24, 25, 26, 27, 28])
            ->orderBy('display_name')
            ->get();
        $users = \App\Models\User::whereHas('claims')->orderBy('name')->get();

        // Get pending claims count for badge
        $pendingCount = Claim::pending()->count();

        // Unread count for finance admins
        $unreadCount = Claim::where('is_admin_read', false)->count();

        return view('admin.claims.index', compact(
            'claims',
            'statistics',
            'categories',
            'users',
            'pendingCount',
            'unreadCount'
        ));
    }

    public function adminShow($id)
    {
        // Check if user is finance admin
        if (!Auth::user()->hasRole('finance_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $claim = Claim::with(['user', 'category', 'receipts', 'approvals.approver', 'auditLogs.user'])
            ->findOrFail($id);

        // Mark as read for admins when opened
        if (!$claim->is_admin_read) {
            $claim->update(['is_admin_read' => true]);
        }

        // Decode category-specific data if exists
        $categoryData = [];
        if ($claim->category_data) {
            $categoryData = json_decode($claim->category_data, true);
        }

        // Get user's claim history for context
        $userClaimHistory = Claim::where('user_id', $claim->user_id)
            ->where('id', '!=', $claim->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.claims.show', compact(
            'claim',
            'categoryData',
            'userClaimHistory'
        ));
    }

    /**
     * Printable view of a single claim for admins.
     */
    public function printAdminClaim($id)
    {
        if (!Auth::user()->hasRole('finance_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $claim = Claim::with(['user', 'category', 'receipts', 'approvals', 'auditLogs'])
            ->findOrFail($id);

        return view('reports.claim_detail', [
            'claim' => $claim,
            'viewer' => 'admin',
        ]);
    }

    public function approve(Request $request, $id)
    {
        // Check if user is finance admin
        if (!Auth::user()->hasRole('finance_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'approved_amount' => 'required|numeric|min:0.01|max:5000.00',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $claim = Claim::findOrFail($id);
            $adminUser = Auth::user();

            // Use the model's approveClaim method
            $claim->approveClaim(
                $request->approved_amount,
                $request->admin_notes,
                $adminUser->id
            );

            Log::info('Claim approved successfully', [
                'claim_id' => $claim->id,
                'approved_by' => $adminUser->id,
                'approved_amount' => $request->approved_amount,
                'original_amount' => $claim->amount
            ]);

            return redirect()
                ->route('admin.claims.show', $claim->id)
                ->with('success', 'Claim approved successfully! Employee will be notified.');

        } catch (\Exception $e) {
            Log::error('Claim approval failed: ' . $e->getMessage(), [
                'claim_id' => $id,
                'approved_by' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to approve claim. ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        // Check if user is finance admin
        if (!Auth::user()->hasRole('finance_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000'
        ]);

        try {
            $claim = Claim::findOrFail($id);
            $adminUser = Auth::user();

            // Use the model's rejectClaim method
            $claim->rejectClaim(
                $request->rejection_reason,
                $adminUser->id
            );

            Log::info('Claim rejected successfully', [
                'claim_id' => $claim->id,
                'rejected_by' => $adminUser->id,
                'rejection_reason' => $request->rejection_reason,
                'amount' => $claim->amount
            ]);

            return redirect()
                ->route('admin.claims.show', $claim->id)
                ->with('success', 'Claim rejected successfully! Employee will be notified.');

        } catch (\Exception $e) {
            Log::error('Claim rejection failed: ' . $e->getMessage(), [
                'claim_id' => $id,
                'rejected_by' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to reject claim. ' . $e->getMessage());
        }
    }

    /**
     * Mark all claims as read for admins.
     */
    public function markAllReadAdmin()
    {
        Claim::where('is_admin_read', false)->update(['is_admin_read' => true]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Export claims for admins for a given month as CSV.
     */
    public function exportAdminClaims(Request $request)
    {
        if (!Auth::user()->hasRole('finance_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $monthInput = $request->input('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $claims = Claim::with(['category', 'user'])
            ->whereBetween('claim_date', [$start, $end])
            ->orderBy('claim_date')
            ->get();

        $fileName = "claims-admin-{$start->format('Y-m')}.csv";

        return response()->streamDownload(function () use ($claims) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Employee', 'Title', 'Category', 'Amount', 'Status', 'Claim Date']);
            foreach ($claims as $claim) {
                fputcsv($handle, [
                    $claim->id,
                    $claim->user->name ?? 'N/A',
                    $claim->title,
                    $claim->category->display_name ?? $claim->category->name ?? 'N/A',
                    $claim->amount,
                    $claim->status,
                    optional($claim->claim_date)->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export admin claims for a given month as PDF.
     */
    public function exportAdminClaimsPdf(Request $request)
    {
        if (!Auth::user()->hasRole('finance_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $monthInput = $request->input('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $claims = Claim::with(['category', 'user'])
            ->whereBetween('claim_date', [$start, $end])
            ->orderBy('claim_date')
            ->get();

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return redirect()->back()->with('error', 'PDF export not available (install barryvdh/laravel-dompdf).');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.claims_summary', [
            'claims' => $claims,
            'title' => 'Claims Summary (Admin)',
            'period' => $start->format('F Y'),
        ]);

        return $pdf->download("claims-admin-{$start->format('Y-m')}.pdf");
    }

    /**
     * Handle category-specific fields dynamically based on the new 10-category structure
     */
    private function handleCategorySpecificFields(Request $request): array
    {
        $categoryData = [];
        $category = ClaimCategory::find($request->category_id);

        if (!$category) {
            return $categoryData;
        }

        // Get all category_data fields from the request
        $submittedCategoryData = $request->input('category_data', []);

        // Ensure we have an array
        if (!is_array($submittedCategoryData)) {
            $submittedCategoryData = [];
        }

        // Map category names to their specific field handling
        $categoryFieldMap = [
            'entertainment_meals' => [
                'customer_name',
                'company_name'
            ],
            'accommodation' => [
                'accommodation_name',
                'date_from',
                'date_to',
                'country',
                'company_name'
            ],
            'transportation_trip' => [
                'transportation_type',
                'destination_from',
                'destination_to',
                'date'
            ],
            'petrol' => [
                'date'
            ],
            'toll' => [
                'date_from',
                'date_to'
            ],
            'phone_bills' => [
                'date_from',
                'date_to'
            ],
            'office_parking' => [
                'date'
            ],
            'client_parking' => [
                'partner_customer_name',
                'date'
            ],
            'medical_claim' => [
                'date'
            ],
            'other_claims' => [
                'company_name',
                'claim_details',
                'date'
            ]
        ];

        // Get the expected fields for this category
        $expectedFields = $categoryFieldMap[$category->name] ?? [];

        // Collect only the expected fields from the submitted category_data
        foreach ($expectedFields as $field) {
            if (isset($submittedCategoryData[$field]) && $submittedCategoryData[$field] !== null && $submittedCategoryData[$field] !== '') {
                $categoryData[$field] = $submittedCategoryData[$field];
            }
        }

        return $categoryData;
    }
}
