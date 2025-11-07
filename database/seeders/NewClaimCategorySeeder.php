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
                    'requires_client_name' => true,
                    'requires_business_purpose' => true,
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
                    'requires_travel_dates' => true,
                    'requires_location' => true,
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
                    'requires_travel_dates' => true,
                    'requires_origin_destination' => true,
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
                    'requires_odometer_reading' => true,
                    'requires_vehicle_details' => true,
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
                    'requires_route_details' => true,
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
                    'requires_billing_period' => true,
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
                    'requires_medical_receipt' => true,
                    'requires_treatment_details' => true,
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
                    'requires_detailed_description' => true,
                    'requires_manager_approval' => true,
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