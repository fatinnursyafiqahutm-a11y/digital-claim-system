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
        // Update entertainment_meals category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'entertainment_meals')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 200.00,
                    'requires_customer_name' => true,
                    'requires_company_name' => true,
                    'requires_location' => true,
                    'requires_attendees_count' => true,
                    'requires_meeting_purpose' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD']
                ])
            ]);

        // Update accommodation category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'accommodation')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_night' => 500.00,
                    'requires_hotel_name' => true,
                    'requires_location' => true,
                    'requires_check_in_date' => true,
                    'requires_check_out_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD', 'EUR']
                ])
            ]);

        // Update transportation_trip category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'transportation_trip')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_trip' => 2000.00,
                    'requires_travel_from' => true,
                    'requires_travel_to' => true,
                    'requires_travel_purpose' => true,
                    'requires_transport_mode' => true,
                    'requires_departure_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD', 'EUR']
                ])
            ]);

        // Update petrol category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'petrol')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 300.00,
                    'requires_vehicle_details' => true,
                    'requires_fuel_type' => true,
                    'requires_odometer_start' => true,
                    'requires_odometer_end' => true,
                    'requires_station_name' => true,
                    'requires_purchase_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR']
                ])
            ]);

        // Update toll category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'toll')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 100.00,
                    'requires_route_from' => true,
                    'requires_route_to' => true,
                    'requires_toll_plaza_name' => true,
                    'requires_travel_date' => true,
                    'requires_vehicle_type' => true,
                    'receipt_required' => false,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        // Update phone_bills category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'phone_bills')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 150.00,
                    'requires_service_provider' => true,
                    'requires_account_number' => true,
                    'requires_billing_period_start' => true,
                    'requires_billing_period_end' => true,
                    'requires_business_usage_percentage' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        // Update office_parking category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'office_parking')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_day' => 20.00,
                    'max_amount_per_month' => 400.00,
                    'requires_parking_location' => true,
                    'requires_parking_type' => true,
                    'requires_start_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        // Update client_parking category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'client_parking')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_visit' => 25.00,
                    'requires_client_name' => true,
                    'requires_client_company' => true,
                    'requires_meeting_purpose' => true,
                    'requires_parking_location' => true,
                    'requires_visit_date' => true,
                    'requires_duration_hours' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        // Update medical_claim category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'medical_claim')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 1000.00,
                    'requires_medical_provider' => true,
                    'requires_patient_name' => true,
                    'requires_treatment_type' => true,
                    'requires_treatment_date' => true,
                    'requires_medical_condition' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        // Update other_claims category validation rules to match frontend fields
        DB::table('claim_categories')
            ->where('name', 'other_claims')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 500.00,
                    'requires_expense_type' => true,
                    'requires_supplier_name' => true,
                    'requires_purchase_date' => true,
                    'requires_expense_details' => true,
                    'requires_justification' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD', 'EUR']
                ])
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original validation rules
        DB::table('claim_categories')
            ->where('name', 'entertainment_meals')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 200.00,
                    'requires_client_name' => true,
                    'requires_meeting_purpose' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD']
                ])
            ]);

        DB::table('claim_categories')
            ->where('name', 'accommodation')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_night' => 500.00,
                    'requires_travel_dates' => true,
                    'requires_location' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD', 'EUR']
                ])
            ]);

        DB::table('claim_categories')
            ->where('name', 'transportation_trip')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_trip' => 2000.00,
                    'requires_travel_dates' => true,
                    'requires_origin_destination' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD', 'EUR']
                ])
            ]);

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
                ])
            ]);

        DB::table('claim_categories')
            ->where('name', 'toll')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 100.00,
                    'requires_route_details' => true,
                    'receipt_required' => false,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        DB::table('claim_categories')
            ->where('name', 'phone_bills')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 150.00,
                    'requires_billing_period' => true,
                    'requires_business_usage_percentage' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        DB::table('claim_categories')
            ->where('name', 'office_parking')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_day' => 20.00,
                    'max_amount_per_month' => 400.00,
                    'requires_parking_location' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        DB::table('claim_categories')
            ->where('name', 'client_parking')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_visit' => 25.00,
                    'requires_client_name' => true,
                    'requires_meeting_purpose' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        DB::table('claim_categories')
            ->where('name', 'medical_claim')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 1000.00,
                    'requires_medical_receipt' => true,
                    'requires_treatment_details' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ])
            ]);

        DB::table('claim_categories')
            ->where('name', 'other_claims')
            ->update([
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 500.00,
                    'requires_detailed_description' => true,
                    'requires_manager_approval' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD', 'EUR']
                ])
            ]);
    }
};