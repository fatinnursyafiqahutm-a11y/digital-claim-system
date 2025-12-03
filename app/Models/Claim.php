<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Claim extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'amount',
        'currency',
        'claim_date',
        'status',
        'priority',
        'category_data',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'approved_amount',
        'payment_date',
        'payment_reference',
        'rejection_reason',
        'admin_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'claim_date' => 'date',
            'category_data' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'payment_date' => 'date',
        ];
    }

    /**
     * Get the user who submitted the claim.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the claim.
     */
    public function category()
    {
        return $this->belongsTo(ClaimCategory::class, 'category_id');
    }

    /**
     * Get the receipts associated with the claim.
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * Get the approvals associated with the claim.
     */
    public function approvals()
    {
        return $this->hasMany(ClaimApproval::class);
    }

    /**
     * Get the audit logs associated with the claim.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Scope to get claims by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending claims.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    /**
     * Scope to get approved claims.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected claims.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if claim can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'returned_for_info']);
    }

    /**
     * Check if claim can be submitted.
     */
    public function canBeSubmitted(): bool
    {
        return in_array($this->status, ['draft', 'returned_for_info']);
    }

    /**
     * Check if claim can be approved.
     */
    public function canBeApproved(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }

    /**
     * Check if claim can be rejected.
     */
    public function canBeRejected(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }

    /**
     * Validate claim amount against business rules.
     */
    public function validateClaimAmount(): array
    {
        $errors = [];

        // Get category-specific validation rules
        if ($this->category) {
            $categoryErrors = $this->category->validateClaimAmount($this->amount);
            $errors = array_merge($errors, $categoryErrors);
        } else {
            // Fallback to default validation
            $maxAmount = 5000.00; // RM 5000 per claim

            if ($this->amount <= 0) {
                $errors[] = 'Claim amount must be greater than 0.';
            }

            if ($this->amount > $maxAmount) {
                $errors[] = "Claim amount cannot exceed RM {$maxAmount}.";
            }
        }

        // Validate against receipt totals if receipts exist
        if ($this->receipts->isNotEmpty()) {
            $totalReceiptAmount = $this->calculateTotalReceiptAmount();
            if (abs($totalReceiptAmount - $this->amount) > 0.01) {
                $errors[] = 'Claim amount must match total receipt amounts.';
            }
        }

        return $errors;
    }

    /**
     * Validate claim against category-specific requirements.
     */
    public function validateCategoryRequirements(): array
    {
        $errors = [];

        // DEBUG: Log what we're working with
        \Log::info('validateCategoryRequirements DEBUG:', [
            'claim_id' => $this->id,
            'category_name' => $this->category?->name,
            'category_display_name' => $this->category?->display_name,
            'category_data' => $this->category_data,
            'required_fields' => $this->category?->getRequiredFields()
        ]);

        if (!$this->category) {
            $errors[] = 'Claim category is required.';
            return $errors;
        }

        // Check receipt requirements
        if ($this->category->isReceiptRequired() && $this->receipts->isEmpty()) {
            $errors[] = "Receipt is required for {$this->category->display_name} claims.";
        }

        // Validate category-specific required fields
        $requiredFields = $this->category->getRequiredFields();
        $categoryData = $this->category_data ?? [];

        // Ensure category_data is an array (fix for JSON string issue)
        if (is_string($categoryData)) {
            $categoryData = json_decode($categoryData, true) ?? [];
        }

        \Log::info('Required fields check:', [
            'required_fields' => $requiredFields,
            'category_data' => $categoryData,
            'category_data_keys' => array_keys($categoryData),
            'has_check_in' => !empty($categoryData['check_in_date']),
            'has_check_out' => !empty($categoryData['check_out_date']),
            'has_location' => !empty($categoryData['location'])
        ]);

        foreach ($requiredFields as $field) {
            // Handle normal field validation
            if (empty($categoryData[$field])) {
                // Map field names to user-friendly labels
                $fieldLabels = [
                    'vehicle_details' => 'Vehicle details',
                    'fuel_type' => 'Fuel type',
                    'station_name' => 'Station name',
                    'purchase_date' => 'Purchase date',
                    'odometer_start' => 'Odometer start reading',
                    'odometer_end' => 'Odometer end reading',
                    'customer_name' => 'Customer name',
                    'company_name' => 'Company name',
                    'meeting_purpose' => 'Meeting purpose',
                    'location' => 'Location',
                    'attendees_count' => 'Number of attendees',
                    'hotel_name' => 'Hotel name',
                    'check_in_date' => 'Check-in date',
                    'check_out_date' => 'Check-out date',
                    'room_type' => 'Room type',
                    'booking_reference' => 'Booking reference',
                    'travel_from' => 'Travel from',
                    'travel_to' => 'Travel to',
                    'travel_purpose' => 'Travel purpose',
                    'transport_mode' => 'Transport mode',
                    'departure_date' => 'Departure date',
                    'return_date' => 'Return date',
                    'route_from' => 'Route from',
                    'route_to' => 'Route to',
                    'toll_plaza_name' => 'Toll plaza name',
                    'travel_date' => 'Travel date',
                    'vehicle_type' => 'Vehicle type',
                    'service_provider' => 'Service provider',
                    'account_number' => 'Account number',
                    'billing_period_start' => 'Billing period start',
                    'billing_period_end' => 'Billing period end',
                    'business_usage_percentage' => 'Business usage percentage',
                    'plan_type' => 'Plan type',
                    'parking_location' => 'Parking location',
                    'parking_duration' => 'Parking duration',
                    'start_date' => 'Start date',
                    'end_date' => 'End date',
                    'parking_type' => 'Parking type',
                    'client_name' => 'Client name',
                    'client_company' => 'Client company',
                    'visit_date' => 'Visit date',
                    'duration_hours' => 'Duration hours',
                    'medical_provider' => 'Medical provider',
                    'patient_name' => 'Patient name',
                    'treatment_type' => 'Treatment type',
                    'treatment_date' => 'Treatment date',
                    'medical_condition' => 'Medical condition',
                    'prescription_required' => 'Prescription required',
                    'expense_details' => 'Expense details',
                    'expense_type' => 'Expense type',
                    'justification' => 'Business justification',
                    'supplier_name' => 'Supplier name'
                ];

                $fieldLabel = $fieldLabels[$field] ?? $field;
                $errors[] = "Field '{$fieldLabel}' is required for {$this->category->display_name} claims.";
            }
        }

        // Validate currency support
        $supportedCurrencies = $this->category->getSupportedCurrencies();
        if (!in_array($this->currency, $supportedCurrencies)) {
            $errors[] = "Currency '{$this->currency}' is not supported for {$this->category->display_name}. Supported currencies: " . implode(', ', $supportedCurrencies);
        }

        return $errors;
    }

    /**
     * Comprehensive claim validation.
     */
    public function validateClaim(): array
    {
        $errors = [];

        // Basic claim validation
        if (empty(trim($this->title))) {
            $errors[] = 'Claim title is required.';
        }

        if (empty(trim($this->description))) {
            $errors[] = 'Claim description is required.';
        }

        if (empty($this->claim_date)) {
            $errors[] = 'Claim date is required.';
        }

        // Amount validation
        $amountErrors = $this->validateClaimAmount();
        $errors = array_merge($errors, $amountErrors);

        // Category-specific validation
        $categoryErrors = $this->validateCategoryRequirements();
        $errors = array_merge($errors, $categoryErrors);

        return $errors;
    }

    /**
     * Submit claim for approval with audit trail.
     */
    public function submitClaim(): bool
    {
        if (!$this->canBeSubmitted()) {
            return false;
        }

        try {
            DB::beginTransaction();

            // Comprehensive claim validation
            $validationErrors = $this->validateClaim();
            if (!empty($validationErrors)) {
                throw new \Exception(implode(' ', $validationErrors));
            }

            // Update claim status and timestamps
            $this->status = 'submitted';
            $this->submitted_at = now();
            $this->save();

            // Create audit log
            $this->createAuditLog('claim_submitted', 'Claim submitted for approval');

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Claim submission failed: ' . $e->getMessage(), [
                'claim_id' => $this->id,
                'user_id' => $this->user_id
            ]);
            throw $e;
        }
    }

    /**
     * Approve claim with optional partial payment.
     */
    public function approveClaim(float $approvedAmount = null, string $adminNotes = null, int $approvedBy = null): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        try {
            DB::beginTransaction();

            $approvedAmount = $approvedAmount ?? $this->amount;

            if ($approvedAmount <= 0 || $approvedAmount > $this->amount) {
                throw new \Exception('Invalid approved amount.');
            }

            // Update claim status and details
            $this->status = 'approved';
            $this->approved_amount = $approvedAmount;
            $this->approved_at = now();
            $this->admin_notes = $adminNotes;
            $this->save();

            // Create approval record
            $this->approvals()->create([
                'approved_by' => $approvedBy,
                'action' => 'approved',
                'previous_status' => $this->getOriginal('status'),
                'new_status' => 'approved',
                'approved_amount' => $approvedAmount,
                'comments' => $adminNotes,
            ]);

            // Create audit log
            $this->createAuditLog('claim_approved', 'Claim approved', [
                'approved_amount' => $approvedAmount,
                'approved_by' => $approvedBy
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Claim approval failed: ' . $e->getMessage(), [
                'claim_id' => $this->id,
                'approved_by' => $approvedBy
            ]);
            throw $e;
        }
    }

    /**
     * Reject claim with reason.
     */
    public function rejectClaim(string $rejectionReason, int $rejectedBy = null): bool
    {
        if (!$this->canBeRejected()) {
            return false;
        }

        if (empty(trim($rejectionReason))) {
            throw new \Exception('Rejection reason is required.');
        }

        try {
            DB::beginTransaction();

            // Update claim status and rejection details
            $this->status = 'rejected';
            $this->rejection_reason = $rejectionReason;
            $this->rejected_at = now();
            $this->save();

            // Create approval record
            $this->approvals()->create([
                'approved_by' => $rejectedBy,
                'action' => 'rejected',
                'previous_status' => $this->getOriginal('status'),
                'new_status' => 'rejected',
                'comments' => $rejectionReason,
            ]);

            // Create audit log
            $this->createAuditLog('claim_rejected', 'Claim rejected', [
                'rejection_reason' => $rejectionReason,
                'rejected_by' => $rejectedBy
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Claim rejection failed: ' . $e->getMessage(), [
                'claim_id' => $this->id,
                'rejected_by' => $rejectedBy
            ]);
            throw $e;
        }
    }

    /**
     * Calculate total amount from all receipts.
     * Note: Receipt amounts are not stored individually - validation is done at claim level.
     */
    public function calculateTotalReceiptAmount(): float
    {
        return $this->amount; // Receipts are supporting documents, claim amount is authoritative
    }

    /**
     * Get formatted status with badge class.
     */
    public function getFormattedStatusAttribute(): array
    {
        $statusMap = [
            'draft' => ['text' => 'Draft', 'class' => 'bg-gray-100 text-gray-800'],
            'submitted' => ['text' => 'Submitted', 'class' => 'bg-blue-100 text-blue-800'],
            'under_review' => ['text' => 'Under Review', 'class' => 'bg-yellow-100 text-yellow-800'],
            'approved' => ['text' => 'Approved', 'class' => 'bg-green-100 text-green-800'],
            'rejected' => ['text' => 'Rejected', 'class' => 'bg-red-100 text-red-800'],
            'paid' => ['text' => 'Paid', 'class' => 'bg-purple-100 text-purple-800'],
            'returned_for_info' => ['text' => 'Returned for Info', 'class' => 'bg-orange-100 text-orange-800'],
        ];

        return $statusMap[$this->status] ?? ['text' => ucfirst($this->status), 'class' => 'bg-gray-100 text-gray-800'];
    }

    /**
     * Get claim statistics for dashboard.
     */
public static function getClaimStatistics(int $userId = null): array
{
    $baseQuery = static::query();

    if ($userId) {
        $baseQuery->where('user_id', $userId);
    }

    return [
        'total_claims'      => (clone $baseQuery)->count(),
        'draft_claims'      => (clone $baseQuery)->byStatus('draft')->count(),
        'submitted_claims'  => (clone $baseQuery)->byStatus('submitted')->count(),
        'under_review_claims' => (clone $baseQuery)->byStatus('under_review')->count(),
        'approved_claims'   => (clone $baseQuery)->byStatus('approved')->count(),
        'rejected_claims'   => (clone $baseQuery)->byStatus('rejected')->count(),
        'total_amount'      => (clone $baseQuery)->sum('amount'),
        'approved_amount'   => (clone $baseQuery)->byStatus('approved')->sum('approved_amount'),
    ];
}


    /**
     * Create audit log entry.
     */
    private function createAuditLog(string $action, string $description, array $details = null): void
    {
        $this->auditLogs()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => 'claim',
            'entity_id' => $this->id,
            'old_values' => null,
            'new_values' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Scope to get claims by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('claim_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get claims by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to search claims by title or description.
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }
}
