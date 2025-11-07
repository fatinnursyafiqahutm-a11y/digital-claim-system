<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
        'validation_rules',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'validation_rules' => 'array',
        ];
    }

    /**
     * Get the claims associated with the category.
     */
    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get validation rules for this category.
     */
    public function getValidationRules(): array
    {
        return $this->validation_rules ?? [];
    }

    /**
     * Get maximum amount allowed for this category.
     */
    public function getMaxAmount(): float
    {
        $rules = $this->getValidationRules();

        // Check various possible max amount fields
        return $rules['max_amount_per_claim']
            ?? $rules['max_amount_per_night']
            ?? $rules['max_amount_per_trip']
            ?? $rules['max_amount_per_day']
            ?? $rules['max_amount_per_month']
            ?? $rules['max_amount_per_visit']
            ?? 5000.00; // Default fallback
    }

    /**
     * Check if receipt is required for this category.
     */
    public function isReceiptRequired(): bool
    {
        $rules = $this->getValidationRules();
        return $rules['receipt_required'] ?? true;
    }

    /**
     * Get required fields for this category.
     */
    public function getRequiredFields(): array
    {
        $rules = $this->getValidationRules();
        $requiredFields = [];

        foreach ($rules as $key => $value) {
            if ($value === true && str_starts_with($key, 'requires_')) {
                $requiredFields[] = str_replace('requires_', '', $key);
            }
        }

        return $requiredFields;
    }

    /**
     * Get supported currencies for this category.
     */
    public function getSupportedCurrencies(): array
    {
        $rules = $this->getValidationRules();
        return $rules['supported_currencies'] ?? ['MYR'];
    }

    /**
     * Validate claim amount against category rules.
     */
    public function validateClaimAmount(float $amount): array
    {
        $errors = [];
        $maxAmount = $this->getMaxAmount();

        if ($amount <= 0) {
            $errors[] = 'Claim amount must be greater than 0.';
        }

        if ($amount > $maxAmount) {
            $errors[] = "Claim amount for {$this->display_name} cannot exceed RM {$maxAmount}.";
        }

        return $errors;
    }

    /**
     * Get category-specific validation rules for forms.
     */
    public function getFormValidationRules(): array
    {
        $baseRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01|max:' . $this->getMaxAmount(),
            'claim_date' => 'required|date|before_or_equal:today',
            'currency' => 'required|in:' . implode(',', $this->getSupportedCurrencies()),
        ];

        // Add category-specific required fields
        $requiredFields = $this->getRequiredFields();
        foreach ($requiredFields as $field) {
            $baseRules["category_data.{$field}"] = 'required';
        }

        // Add receipt requirement if applicable
        if ($this->isReceiptRequired()) {
            $baseRules['receipts'] = 'required|array|min:1';
            $baseRules['receipts.*'] = 'file|mimes:jpg,jpeg,png,pdf|max:5120'; // 5MB max
        }

        return $baseRules;
    }

    /**
     * Get category display information with validation details.
     */
    public function getDisplayInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'max_amount' => $this->getMaxAmount(),
            'receipt_required' => $this->isReceiptRequired(),
            'required_fields' => $this->getRequiredFields(),
            'supported_currencies' => $this->getSupportedCurrencies(),
            'validation_rules' => $this->getValidationRules(),
        ];
    }
}
