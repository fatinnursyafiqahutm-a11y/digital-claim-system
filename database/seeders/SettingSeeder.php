<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'max_claim_amount',
                'value' => '5000.00',
                'description' => 'Maximum claim amount per submission',
            ],
            [
                'key' => 'receipt_required',
                'value' => 'true',
                'description' => 'Whether receipt upload is mandatory for claims',
            ],
            [
                'key' => 'auto_approve_below',
                'value' => '100.00',
                'description' => 'Claims below this amount are automatically approved',
            ],
            [
                'key' => 'default_currency',
                'value' => 'MYR',
                'description' => 'Default currency for claims',
            ],
            [
                'key' => 'company_name',
                'value' => 'Digital Claim System',
                'description' => 'Company name for reports and communications',
            ],
            [
                'key' => 'max_file_size',
                'value' => '5120',
                'description' => 'Maximum file size for receipt uploads in KB',
            ],
            [
                'key' => 'allowed_file_types',
                'value' => 'jpg,jpeg,png,pdf,doc,docx',
                'description' => 'Allowed file types for receipt uploads',
            ],
            [
                'key' => 'claim_retention_days',
                'value' => '2555',
                'description' => 'Number of days to retain claim records (7 years)',
            ],
            [
                'key' => 'email_notifications_enabled',
                'value' => 'true',
                'description' => 'Enable email notifications for claim status changes',
            ],
            [
                'key' => 'system_timezone',
                'value' => 'Asia/Kuala_Lumpur',
                'description' => 'Default timezone for the system',
            ],
        ];

        foreach ($settings as $setting) {
            \DB::table('settings')->insert($setting);
        }
    }
}
