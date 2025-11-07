<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadReceiptRequest extends FormRequest
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

        // User can only upload receipts to their own claims
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
        $currentReceiptCount = $claim->receipts()->count();

        return [
            'receipts' => [
                'required',
                'array',
                'min:1',
                'max:' . (5 - $currentReceiptCount) // Maximum 5 receipts per claim total
            ],
            'receipts.*' => [
                'required',
                'file',
                'max:5120', // 5MB max per file
                'mimes:jpeg,jpg,png,pdf'
            ],
            'set_primary' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        $claim = $this->route('claim');
        $currentReceiptCount = $claim->receipts()->count();
        $remainingSlots = 5 - $currentReceiptCount;

        return [
            'receipts.required' => 'At least one receipt must be uploaded.',
            'receipts.min' => 'At least one receipt must be uploaded.',
            'receipts.max' => "You can only upload {$remainingSlots} more receipt(s). Maximum 5 receipts per claim.",
            'receipts.*.required' => 'All receipt files are required.',
            'receipts.*.max' => 'Each receipt file cannot exceed 5MB.',
            'receipts.*.mimes' => 'Receipts must be JPEG, PNG, or PDF files.',
            'receipts.*.file' => 'Invalid file format.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'receipts.*' => 'receipt file',
            'set_primary' => 'set as primary',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $claim = $this->route('claim');

            // Validate that claim hasn't been submitted for approval
            if ($claim && !$claim->canBeEdited()) {
                $validator->errors()->add('claim_status',
                    'Receipts cannot be uploaded because this claim has already been submitted for approval.');
            }

            // Check for duplicate files
            if ($this->hasFile('receipts')) {
                foreach ($this->file('receipts') as $index => $receipt) {
                    $fileHash = hash_file('sha256', $receipt->getPathname());

                    $existingReceipt = $claim->receipts()
                        ->where('file_hash', $fileHash)
                        ->first();

                    if ($existingReceipt) {
                        $validator->errors()->add("receipts.{$index}",
                            'This file has already been uploaded for this claim.');
                    }
                }
            }

            // Additional validation for image files
            if ($this->hasFile('receipts')) {
                foreach ($this->file('receipts') as $index => $receipt) {
                    // Check if file is actually an image when extension indicates so
                    $extension = strtolower($receipt->getClientOriginalExtension());
                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        if (!@getimagesize($receipt->getPathname())) {
                            $validator->errors()->add("receipts.{$index}",
                                'The uploaded image file appears to be corrupted or invalid.');
                        }
                    }

                    // Check for empty files
                    if ($receipt->getSize() === 0) {
                        $validator->errors()->add("receipts.{$index}",
                            'The uploaded file is empty.');
                    }
                }
            }

            // Validate primary receipt setting
            if ($this->boolean('set_primary') && $this->file('receipts')) {
                $receiptCount = count($this->file('receipts'));
                if ($receiptCount > 1) {
                    $validator->errors()->add('set_primary',
                        'Cannot set multiple receipts as primary. Please upload one receipt at a time to set as primary.');
                }
            }
        });
    }
}