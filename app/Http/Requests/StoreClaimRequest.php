<?php

namespace App\Http\Requests;

use App\Models\ClaimCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('employee');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // DEBUG: Log incoming request data
        \Log::info('Claim Request Data:', [
            'all_data' => $this->all(),
            'category_id' => $this->category_id,
            'category_data' => $this->category_data,
            'has_category_data' => $this->has('category_data'),
            'input_method' => $this->method()
        ]);

        $category = ClaimCategory::find($this->category_id);

        // Base validation rules that apply to all claims
        $baseRules = [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
                'min:10'
            ],
            'amount' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:' . ($category ? $category->getMaxAmount() : 5000.00)
            ],
            'claim_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:-30 days' // Claims within last 30 days only
            ],
            'category_id' => [
                'required',
                'exists:claim_categories,id'
            ],
            'priority' => [
                'nullable',
                'string',
                Rule::in(['low', 'normal', 'high', 'urgent'])
            ],
            'currency' => [
                'required',
                'string',
                Rule::in($category ? $category->getSupportedCurrencies() : ['MYR'])
            ],
            'category_data' => [
                'nullable',
                'array'
            ],
        ];

        // Receipt validation - conditional based on category requirements
        if ($category && $category->isReceiptRequired()) {
            $baseRules['receipts'] = [
                'required',
                'array',
                'min:1',
                'max:5' // Maximum 5 receipts per claim
            ];
            $baseRules['receipts.*'] = [
                'required',
                'file',
                'max:5120', // 5MB max per file
                'mimes:jpeg,jpg,png,pdf'
            ];
        } else {
            $baseRules['receipts'] = [
                'nullable',
                'array',
                'max:5'
            ];
            $baseRules['receipts.*'] = [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpeg,jpg,png,pdf'
            ];
        }

        // Add category-specific validation rules
        if ($category) {
            $categoryRules = $this->getCategorySpecificRules($category);
            $baseRules = array_merge($baseRules, $categoryRules);
        }

        return $baseRules;
    }

    /**
     * Get category-specific validation rules based on the new 10-category structure
     */
    private function getCategorySpecificRules(ClaimCategory $category): array
    {
        $rules = [];
        $requiredFields = $category->getRequiredFields();

        // Map category names to their specific validation rules
        $categorySpecificRules = [
            'entertainment_meals' => [
                'category_data.customer_name' => ['required', 'string', 'max:255'],
                'category_data.company_name' => ['required', 'string', 'max:255'],
                'category_data.meeting_purpose' => ['required', 'string', 'max:500'],
                'category_data.location' => ['nullable', 'string', 'max:255'],
                'category_data.attendees_count' => ['nullable', 'integer', 'min:1', 'max:50'],
            ],
            'accommodation' => [
                'category_data.hotel_name' => ['required', 'string', 'max:255'],
                'category_data.location' => ['required', 'string', 'max:255'],
                'category_data.check_in_date' => ['required', 'date', 'before_or_equal:today'],
                'category_data.check_out_date' => ['required', 'date', 'after:category_data.check_in_date'],
                'category_data.room_type' => ['nullable', 'string', 'max:100'],
                'category_data.booking_reference' => ['nullable', 'string', 'max:100'],
            ],
            'transportation_trip' => [
                'category_data.travel_from' => ['required', 'string', 'max:255'],
                'category_data.travel_to' => ['required', 'string', 'max:255'],
                'category_data.travel_purpose' => ['required', 'string', 'max:500'],
                'category_data.transport_mode' => ['required', 'string', Rule::in(['flight', 'train', 'bus', 'taxi', 'rental_car', 'other'])],
                'category_data.departure_date' => ['required', 'date', 'before_or_equal:today'],
                'category_data.return_date' => ['nullable', 'date', 'after_or_equal:category_data.departure_date'],
                'category_data.booking_reference' => ['nullable', 'string', 'max:100'],
            ],
            'petrol' => [
                'category_data.vehicle_details' => ['required', 'string', 'max:255'],
                'category_data.odometer_start' => ['required', 'integer', 'min:0'],
                'category_data.odometer_end' => ['required', 'integer', 'gt:category_data.odometer_start'],
                'category_data.fuel_type' => ['required', 'string', Rule::in(['petrol', 'diesel', 'hybrid', 'electric'])],
                'category_data.station_name' => ['required', 'string', 'max:255'],
                'category_data.purchase_date' => ['required', 'date', 'before_or_equal:today'],
            ],
            'toll' => [
                'category_data.route_from' => ['required', 'string', 'max:255'],
                'category_data.route_to' => ['required', 'string', 'max:255'],
                'category_data.toll_plaza_name' => ['required', 'string', 'max:255'],
                'category_data.travel_date' => ['required', 'date', 'before_or_equal:today'],
                'category_data.vehicle_type' => ['required', 'string', Rule::in(['motorcycle', 'car', 'van', 'truck'])],
            ],
            'phone_bills' => [
                'category_data.service_provider' => ['required', 'string', 'max:255'],
                'category_data.account_number' => ['required', 'string', 'max:100'],
                'category_data.billing_period_start' => ['required', 'date', 'before_or_equal:today'],
                'category_data.billing_period_end' => ['required', 'date', 'after_or_equal:category_data.billing_period_start'],
                'category_data.business_usage_percentage' => ['required', 'integer', 'min:0', 'max:100'],
                'category_data.plan_type' => ['nullable', 'string', 'max:100'],
            ],
            'office_parking' => [
                'category_data.parking_location' => ['required', 'string', 'max:255'],
                'category_data.parking_duration' => ['required', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
                'category_data.start_date' => ['required', 'date', 'before_or_equal:today'],
                'category_data.end_date' => ['nullable', 'date', 'after_or_equal:category_data.start_date'],
                'category_data.parking_type' => ['nullable', 'string', Rule::in(['covered', 'open', 'seasonal'])],
            ],
            'client_parking' => [
                'category_data.client_name' => ['required', 'string', 'max:255'],
                'category_data.client_company' => ['required', 'string', 'max:255'],
                'category_data.meeting_purpose' => ['required', 'string', 'max:500'],
                'category_data.parking_location' => ['required', 'string', 'max:255'],
                'category_data.visit_date' => ['required', 'date', 'before_or_equal:today'],
                'category_data.duration_hours' => ['required', 'integer', 'min:1', 'max:24'],
            ],
            'medical_claim' => [
                'category_data.medical_provider' => ['required', 'string', 'max:255'],
                'category_data.patient_name' => ['required', 'string', 'max:255'],
                'category_data.treatment_type' => ['required', 'string', 'max:255'],
                'category_data.treatment_date' => ['required', 'date', 'before_or_equal:today'],
                'category_data.medical_condition' => ['nullable', 'string', 'max:500'],
                'category_data.prescription_required' => ['nullable', 'boolean'],
            ],
            'other_claims' => [
                'category_data.expense_details' => ['required', 'string', 'max:1000'],
                'category_data.expense_type' => ['required', 'string', 'max:255'],
                'category_data.justification' => ['required', 'string', 'max:1000'],
                'category_data.supplier_name' => ['required', 'string', 'max:255'],
                'category_data.purchase_date' => ['required', 'date', 'before_or_equal:today'],
            ]
        ];

        return $categorySpecificRules[$category->name] ?? [];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Claim title is required.',
            'title.min' => 'Claim title must be at least 3 characters long.',
            'description.required' => 'Claim description is required.',
            'description.min' => 'Claim description must be at least 10 characters long.',
            'amount.required' => 'Claim amount is required.',
            'amount.min' => 'Claim amount must be greater than 0.',
            'amount.max' => 'Claim amount cannot exceed RM 5000.',
            'claim_date.before_or_equal' => 'Claim date cannot be in the future.',
            'claim_date.after_or_equal' => 'Claim date cannot be more than 30 days old.',
            'category_id.required' => 'Please select a claim category.',
            'category_id.exists' => 'Selected category is invalid.',
            'receipts.required' => 'At least one receipt must be uploaded.',
            'receipts.min' => 'At least one receipt must be uploaded.',
            'receipts.max' => 'Maximum 5 receipts can be uploaded per claim.',
            'receipts.*.required' => 'All receipt files are required.',
            'receipts.*.max' => 'Each receipt file cannot exceed 5MB.',
            'receipts.*.mimes' => 'Receipts must be JPEG, PNG, or PDF files.',
            // Accommodation specific error messages
            'category_data.hotel_name.required' => 'Hotel name is required for accommodation claims.',
            'category_data.location.required' => 'Location is required for accommodation claims.',
            'category_data.check_in_date.required' => 'Check-in date is required for accommodation claims.',
            'category_data.check_out_date.required' => 'Check-out date is required for accommodation claims.',
            'category_data.check_in_date.before_or_equal' => 'Check-in date cannot be in the future.',
            'category_data.check_out_date.after' => 'Check-out date must be after check-in date.',
            // Entertainment messages
            'category_data.customer_name.required' => 'Customer name is required for entertainment claims.',
            'category_data.company_name.required' => 'Company name is required for entertainment claims.',
            'category_data.meeting_purpose.required' => 'Meeting purpose is required for entertainment claims.',
            // Transportation messages
            'category_data.travel_from.required' => 'Travel origin is required for transportation claims.',
            'category_data.travel_to.required' => 'Travel destination is required for transportation claims.',
            'category_data.travel_purpose.required' => 'Travel purpose is required for transportation claims.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'claim title',
            'description' => 'claim description',
            'amount' => 'claim amount',
            'claim_date' => 'claim date',
            'category_id' => 'claim category',
            'receipts.*' => 'receipt file',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Handle special validation for accommodation category - only for cross-field validation
            $category = ClaimCategory::find($this->category_id);

            if ($category && $category->name === 'accommodation') {
                // Cross-field validation: check-out date must be after check-in date
                $checkInDate = $this->input('category_data.check_in_date');
                $checkOutDate = $this->input('category_data.check_out_date');

                if ($checkInDate && $checkOutDate && strtotime($checkOutDate) <= strtotime($checkInDate)) {
                    $validator->errors()->add('category_data.check_out_date', 'Check-out date must be after check-in date.');
                }
            }

            // Validate that total claim amount is reasonable for receipts
            if ($this->hasFile('receipts')) {
                $totalFileSize = 0;
                foreach ($this->file('receipts') as $receipt) {
                    $totalFileSize += $receipt->getSize();
                }

                // Check if total file size is reasonable for the claim amount
                if ($totalFileSize < 1024 && $this->amount > 100) {
                    $validator->errors()->add('amount',
                        'High claim amount with very small receipt files. Please ensure receipts support the claimed amount.');
                }
            }

            // Additional business rule validation based on claim amount
            if ($this->amount > 1000 && $this->description && strlen($this->description) < 50) {
                $validator->errors()->add('description',
                    'Detailed description (at least 50 characters) is required for claims exceeding RM 1000.');
            }
        });
    }
}