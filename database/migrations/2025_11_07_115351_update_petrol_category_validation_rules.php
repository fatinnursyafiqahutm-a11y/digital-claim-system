<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the petrol category validation rules to fix odometer field mismatch
        DB::table('claim_categories')
            ->where('name', 'petrol')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 300.00,
                    'requires_odometer_start' => true,
                    'requires_odometer_end' => true,
                    'requires_vehicle_details' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ]),
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the old validation rules
        DB::table('claim_categories')
            ->where('name', 'petrol')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 300.00,
                    'requires_odometer_reading' => true,
                    'requires_vehicle_details' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ]),
                'updated_at' => now()
            ]);
    }
};
