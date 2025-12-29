<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update Entertainment validation rules
        DB::table('claim_categories')
            ->where('name', 'entertainment_meals')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 200.00,
                    'requires_customer_name' => true,
                    'requires_company_name' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update Accommodation validation rules
        DB::table('claim_categories')
            ->where('name', 'accommodation')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_night' => 500.00,
                    'requires_accommodation_name' => true,
                    'requires_date_from' => true,
                    'requires_date_to' => true,
                    'requires_country' => true,
                    'requires_company_name' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update Transportation validation rules
        DB::table('claim_categories')
            ->where('name', 'transportation_trip')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_trip' => 2000.00,
                    'requires_transportation_type' => true,
                    'requires_destination_from' => true,
                    'requires_destination_to' => true,
                    'requires_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update Petrol validation rules
        DB::table('claim_categories')
            ->where('name', 'petrol')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 300.00,
                    'requires_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update Toll validation rules
        DB::table('claim_categories')
            ->where('name', 'toll')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 100.00,
                    'requires_date_from' => true,
                    'requires_date_to' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update Phone Bills validation rules
        DB::table('claim_categories')
            ->where('name', 'phone_bills')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 150.00,
                    'requires_date_from' => true,
                    'requires_date_to' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update Office Parking validation rules
        DB::table('claim_categories')
            ->where('name', 'office_parking')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_month' => 400.00,
                    'requires_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update Client Parking validation rules
        DB::table('claim_categories')
            ->where('name', 'client_parking')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_visit' => 25.00,
                    'requires_partner_customer_name' => true,
                    'requires_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update Medical Claim validation rules
        DB::table('claim_categories')
            ->where('name', 'medical_claim')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 1000.00,
                    'requires_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update Other Claims validation rules
        DB::table('claim_categories')
            ->where('name', 'other_claims')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 500.00,
                    'requires_company_name' => true,
                    'requires_claim_details' => true,
                    'requires_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration updates validation rules to simplified fields
        // Reverting would restore old complex validation rules, which we don't want
        // So we'll leave this empty or restore to a previous state if needed
    }
};
