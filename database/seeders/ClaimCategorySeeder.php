<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClaimCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'travel',
                'display_name' => 'Travel Expenses',
                'description' => 'Transportation, accommodation, and related travel expenses',
            ],
            [
                'name' => 'medical',
                'display_name' => 'Medical Expenses',
                'description' => 'Medical consultations, treatments, and medications',
            ],
            [
                'name' => 'meals',
                'display_name' => 'Meals & Entertainment',
                'description' => 'Business meals, client entertainment, and refreshments',
            ],
            [
                'name' => 'office_supplies',
                'display_name' => 'Office Supplies',
                'description' => 'Stationery, printer ink, and other office materials',
            ],
            [
                'name' => 'training',
                'display_name' => 'Training & Development',
                'description' => 'Professional courses, workshops, and certifications',
            ],
            [
                'name' => 'communication',
                'display_name' => 'Communication',
                'description' => 'Internet bills, phone charges, and communication tools',
            ],
            [
                'name' => 'equipment',
                'display_name' => 'Equipment & Tools',
                'description' => 'Computer hardware, software licenses, and work equipment',
            ],
            [
                'name' => 'other',
                'display_name' => 'Other Expenses',
                'description' => 'Miscellaneous business expenses not covered by other categories',
            ],
        ];

        foreach ($categories as $category) {
            \DB::table('claim_categories')->insert($category);
        }
    }
}
