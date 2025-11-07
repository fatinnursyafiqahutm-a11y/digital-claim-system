<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewClaimCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories if needed (for fresh installs)
        if ($this->command->confirm('Do you want to clear existing claim categories?')) {
            DB::table('claim_categories')->delete();
        }

        $categories = [
            [
                'name' => 'entertainment_meals',
                'display_name' => 'Entertainment (Meals)',
                'description' => 'Business meals with clients, entertainment expenses, and team-building food expenses',
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 200.00,
                    'requires_customer_name' => true,
                    'requires_company_name' => true,
                    'requires_location' => true,
                    'requires_attendees_count' => true,
                    'requires_meeting_purpose' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD']
                ]),
            ],
            [
                'name' => 'accommodation',
                'display_name' => 'Accommodation',
                'description' => 'Hotel rooms, serviced apartments, and other lodging expenses during business travel',
                'validation_rules' => json_encode([
                    'max_amount_per_night' => 500.00,
                    'requires_hotel_name' => true,
                    'requires_location' => true,
                    'requires_check_in_date' => true,
                    'requires_check_out_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD', 'EUR']
                ]),
            ],
            [
                'name' => 'transportation_trip',
                'display_name' => 'Transportation/Trip',
                'description' => 'Flights, trains, buses, taxis, and other transportation expenses for business travel',
                'validation_rules' => json_encode([
                    'max_amount_per_trip' => 2000.00,
                    'requires_travel_from' => true,
                    'requires_travel_to' => true,
                    'requires_travel_purpose' => true,
                    'requires_transport_mode' => true,
                    'requires_departure_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD', 'EUR']
                ]),
            ],
            [
                'name' => 'petrol',
                'display_name' => 'Petrol',
                'description' => 'Fuel and gasoline expenses for business-related vehicle usage',
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
                ]),
            ],
            [
                'name' => 'toll',
                'display_name' => 'Toll',
                'description' => 'Highway tolls, bridge fees, and other road usage charges for business travel',
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 100.00,
                    'requires_route_from' => true,
                    'requires_route_to' => true,
                    'requires_toll_plaza_name' => true,
                    'requires_travel_date' => true,
                    'requires_vehicle_type' => true,
                    'receipt_required' => false, // Often no receipts for tolls
                    'supported_currencies' => ['MYR', 'SGD']
                ]),
            ],
            [
                'name' => 'phone_bills',
                'display_name' => 'Phone Bills',
                'description' => 'Mobile phone and landline bills for business communication',
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 150.00,
                    'requires_service_provider' => true,
                    'requires_account_number' => true,
                    'requires_billing_period_start' => true,
                    'requires_billing_period_end' => true,
                    'requires_business_usage_percentage' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ]),
            ],
            [
                'name' => 'office_parking',
                'display_name' => 'Office Parking',
                'description' => 'Parking expenses at or near the office premises',
                'validation_rules' => json_encode([
                    'max_amount_per_day' => 20.00,
                    'max_amount_per_month' => 400.00,
                    'requires_parking_location' => true,
                    'requires_parking_type' => true,
                    'requires_start_date' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ]),
            ],
            [
                'name' => 'client_parking',
                'display_name' => 'Client Parking',
                'description' => 'Parking expenses when visiting client locations for business meetings',
                'validation_rules' => json_encode([
                    'max_amount_per_visit' => 25.00,
                    'requires_client_name' => true,
                    'requires_meeting_purpose' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ]),
            ],
            [
                'name' => 'medical_claim',
                'display_name' => 'Medical Claim',
                'description' => 'Medical consultations, treatments, medications, and other health-related expenses',
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 1000.00,
                    'requires_medical_provider' => true,
                    'requires_patient_name' => true,
                    'requires_treatment_type' => true,
                    'requires_treatment_date' => true,
                    'requires_medical_condition' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD']
                ]),
            ],
            [
                'name' => 'other_claims',
                'display_name' => 'Other Claims',
                'description' => 'Miscellaneous business expenses not covered by other categories',
                'validation_rules' => json_encode([
                    'max_amount_per_claim' => 500.00,
                    'requires_expense_type' => true,
                    'requires_supplier_name' => true,
                    'requires_purchase_date' => true,
                    'requires_expense_details' => true,
                    'requires_justification' => true,
                    'receipt_required' => true,
                    'supported_currencies' => ['MYR', 'SGD', 'USD', 'EUR']
                ]),
            ],
        ];

        foreach ($categories as $category) {
            DB::table('claim_categories')->insert(array_merge($category, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Successfully seeded 10 new claim categories!');
    }
}