<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $claim = $this->route('claim');

        // User must be logged in and be an employee
        if (!auth()->check() || !auth()->user()->hasRole('employee')) {
            return false;
        }

        // User can only edit their own claims
        if ($claim->user_id !== auth()->id()) {
            return false;
        }

        // Claim must be in editable status
        return $claim->canBeEdited();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $claim = $this->route('claim');

        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'max:1000',
                'min:10'
            ],
            'amount' => [
                'sometimes',
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:5000.00'
            ],
            'currency' => [
                'sometimes',
                'required',
                'string',
                'size:3'
            ],
            'claim_date' => [
                'sometimes',
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:-30 days'
            ],
            'category_id' => [
                'sometimes',
                'required',
                'exists:claim_categories,id'
            ],
            'priority' => [
                'nullable',
                'string',
                Rule::in(['low', 'normal', 'high', 'urgent'])
            ],
            'customer_name' => [
                'nullable',
                'required_if:category_id,1',
                'string',
                'max:255'
            ],
            'company_name' => [
                'nullable',
                'required_if:category_id,1',
                'string',
                'max:255'
            ],
            'meeting_purpose' => [
                'nullable',
                'required_if:category_id,1',
                'string',
                'max:500'
            ],
            'travel_from' => [
                'nullable',
                'required_if:category_id,2',
                'string',
                'max:255'
            ],
            'travel_to' => [
                'nullable',
                'required_if:category_id,2',
                'string',
                'max:255'
            ],
            'travel_purpose' => [
                'nullable',
                'required_if:category_id,2',
                'string',
                'max:500'
            ],
            'hotel_name' => [
                'nullable',
                'required_if:category_id,3',
                'string',
                'max:255'
            ],
            'check_in_date' => [
                'nullable',
                'required_if:category_id,3',
                'date',
                'before_or_equal:today'
            ],
            'check_out_date' => [
                'nullable',
                'required_if:category_id,3',
                'date',
                'after:check_in_date'
            ],
        ];
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
            'customer_name.required_if' => 'Customer name is required for entertainment claims.',
            'company_name.required_if' => 'Company name is required for entertainment claims.',
            'meeting_purpose.required_if' => 'Meeting purpose is required for entertainment claims.',
            'travel_from.required_if' => 'Travel origin is required for transportation claims.',
            'travel_to.required_if' => 'Travel destination is required for transportation claims.',
            'travel_purpose.required_if' => 'Travel purpose is required for transportation claims.',
            'hotel_name.required_if' => 'Hotel name is required for accommodation claims.',
            'check_in_date.required_if' => 'Check-in date is required for accommodation claims.',
            'check_out_date.required_if' => 'Check-out date is required for accommodation claims.',
            'check_out_date.after' => 'Check-out date must be after check-in date.',
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
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $claim = $this->route('claim');

            // Additional business rule validation based on claim amount
            if ($this->has('amount') && $this->amount > 1000) {
                $description = $this->get('description', $claim->description);
                if (strlen($description) < 50) {
                    $validator->errors()->add('description',
                        'Detailed description (at least 50 characters) is required for claims exceeding RM 1000.');
                }
            }

            // Validate that claim hasn't been submitted for approval
            if ($claim && !$claim->canBeEdited()) {
                $validator->errors()->add('status',
                    'This claim cannot be edited because it has already been submitted for approval.');
            }
        });
    }
}