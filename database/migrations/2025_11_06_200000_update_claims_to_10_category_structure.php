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
        DB::transaction(function () {
            // Step 1: Create backup of existing categories for rollback
            DB::statement('CREATE TABLE claim_categories_backup AS SELECT * FROM claim_categories');

            // Step 2: Insert new 10 categories
            $newCategories = [
                ['name' => 'entertainment_meals', 'display_name' => 'Entertainment (Meals)', 'description' => 'Business meals and entertainment expenses'],
                ['name' => 'accommodation', 'display_name' => 'Accommodation', 'description' => 'Hotel and lodging expenses for business travel'],
                ['name' => 'transportation_trip', 'display_name' => 'Transportation/Trip', 'description' => 'Travel transportation expenses including flights, trains, buses'],
                ['name' => 'petrol', 'display_name' => 'Petrol', 'description' => 'Fuel and gasoline expenses for business vehicles'],
                ['name' => 'toll', 'display_name' => 'Toll', 'description' => 'Toll road and highway charges'],
                ['name' => 'phone_bills', 'display_name' => 'Phone Bills', 'description' => 'Mobile phone and landline business expenses'],
                ['name' => 'office_parking', 'display_name' => 'Office Parking', 'description' => 'Parking expenses at office premises'],
                ['name' => 'client_parking', 'display_name' => 'Client Parking', 'description' => 'Parking expenses at client locations'],
                ['name' => 'medical_claim', 'display_name' => 'Medical Claim', 'description' => 'Medical expenses and health-related claims'],
                ['name' => 'other_claims', 'display_name' => 'Other Claims', 'description' => 'Miscellaneous business expenses not covered by other categories'],
            ];

            foreach ($newCategories as $category) {
                DB::table('claim_categories')->insert($category + [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Step 3: Create migration mapping table for tracking
            DB::statement('
                CREATE TABLE category_migration_mapping (
                    old_category_id INTEGER,
                    old_category_name VARCHAR(50),
                    new_category_id INTEGER,
                    new_category_name VARCHAR(50),
                    migration_reason TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            // Step 4: Map existing claims to new categories
            $this->migrateExistingClaims();

            // Step 5: Deactivate old categories (don't delete to maintain data integrity)
            $oldCategoryNames = ['travel', 'meals', 'medical', 'office_supplies', 'training', 'communication', 'equipment', 'other'];
            DB::table('claim_categories')
                ->whereIn('name', $oldCategoryNames)
                ->update(['is_active' => false, 'updated_at' => now()]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function () {
            // Step 1: Reactivate old categories
            $oldCategoryNames = ['travel', 'meals', 'medical', 'office_supplies', 'training', 'communication', 'equipment', 'other'];
            DB::table('claim_categories')
                ->whereIn('name', $oldCategoryNames)
                ->update(['is_active' => true, 'updated_at' => now()]);

            // Step 2: Restore original category mappings using migration mapping table
            $this->restoreOriginalCategories();

            // Step 3: Remove new categories
            $newCategoryNames = [
                'entertainment_meals', 'accommodation', 'transportation_trip',
                'petrol', 'toll', 'phone_bills', 'office_parking',
                'client_parking', 'medical_claim', 'other_claims'
            ];
            DB::table('claim_categories')
                ->whereIn('name', $newCategoryNames)
                ->delete();

            // Step 4: Drop migration tracking tables
            DB::statement('DROP TABLE IF EXISTS category_migration_mapping');
            DB::statement('DROP TABLE IF EXISTS claim_categories_backup');
        });
    }

    /**
     * Migrate existing claims to new categories based on business rules
     */
    private function migrateExistingClaims(): void
    {
        // Category mapping: old_id => new_id, migration_reason
        $categoryMappings = [
            1 => ['new_id' => null, 'reason' => 'Travel claims need manual review - may contain accommodation, transport, petrol, toll'],
            2 => ['new_id' => null, 'reason' => 'Medical claims map directly but need verification'],
            3 => ['new_id' => null, 'reason' => 'Meals claims map directly to Entertainment (Meals)'],
            4 => ['new_id' => null, 'reason' => 'Office supplies fall under Other Claims'],
            5 => ['new_id' => null, 'reason' => 'Training expenses fall under Other Claims'],
            6 => ['new_id' => null, 'reason' => 'Communication claims need manual review - may be phone bills or other'],
            7 => ['new_id' => null, 'reason' => 'Equipment expenses fall under Other Claims'],
            8 => ['new_id' => null, 'reason' => 'Other expenses map directly'],
        ];

        // Get the new category IDs
        $newCategories = DB::table('claim_categories')
            ->whereIn('name', [
                'entertainment_meals', 'accommodation', 'transportation_trip',
                'petrol', 'toll', 'phone_bills', 'office_parking',
                'client_parking', 'medical_claim', 'other_claims'
            ])
            ->pluck('id', 'name')
            ->toArray();

        // Update mappings with actual new category IDs
        $categoryMappings[3]['new_id'] = $newCategories['entertainment_meals']; // meals → Entertainment (Meals)
        $categoryMappings[2]['new_id'] = $newCategories['medical_claim']; // medical → Medical Claim
        $categoryMappings[8]['new_id'] = $newCategories['other_claims']; // other → Other Claims
        $categoryMappings[4]['new_id'] = $newCategories['other_claims']; // office_supplies → Other Claims
        $categoryMappings[5]['new_id'] = $newCategories['other_claims']; // training → Other Claims
        $categoryMappings[7]['new_id'] = $newCategories['other_claims']; // equipment → Other Claims

        // For categories that need manual review, set them to Transportation/Trip for now
        $categoryMappings[1]['new_id'] = $newCategories['transportation_trip']; // travel → Transportation/Trip (needs manual review)
        $categoryMappings[6]['new_id'] = $newCategories['phone_bills']; // communication → Phone Bills (needs manual review)

        // Get old category names for logging
        $oldCategories = DB::table('claim_categories_backup')
            ->pluck('name', 'id')
            ->toArray();

        // Perform the migration
        foreach ($categoryMappings as $oldId => $mapping) {
            if ($mapping['new_id']) {
                // Update existing claims
                $updatedClaims = DB::table('claims')
                    ->where('category_id', $oldId)
                    ->update([
                        'category_id' => $mapping['new_id'],
                        'updated_at' => now(),
                    ]);

                // Log the migration mapping
                DB::table('category_migration_mapping')->insert([
                    'old_category_id' => $oldId,
                    'old_category_name' => $oldCategories[$oldId] ?? 'Unknown',
                    'new_category_id' => $mapping['new_id'],
                    'new_category_name' => DB::table('claim_categories')->find($mapping['new_id'])->name ?? 'Unknown',
                    'migration_reason' => $mapping['reason'],
                    'created_at' => now(),
                ]);

                // Create audit log for category changes
                if ($updatedClaims > 0) {
                    $this->createAuditLogForCategoryChange($oldId, $mapping['new_id'], $updatedClaims);
                }
            }
        }
    }

    /**
     * Restore original categories from backup
     */
    private function restoreOriginalCategories(): void
    {
        $migrations = DB::table('category_migration_mapping')->get();

        foreach ($migrations as $migration) {
            DB::table('claims')
                ->where('category_id', $migration->new_category_id)
                ->whereRaw('id IN (SELECT id FROM claims WHERE updated_at >= ?)', [$migration->created_at])
                ->update([
                    'category_id' => $migration->old_category_id,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Create audit log entries for category migration
     */
    private function createAuditLogForCategoryChange(int $oldCategoryId, int $newCategoryId, int $affectedClaims): void
    {
        $oldCategory = DB::table('claim_categories_backup')->find($oldCategoryId);
        $newCategory = DB::table('claim_categories')->find($newCategoryId);

        DB::table('audit_logs')->insert([
            'user_id' => 1, // System user
            'claim_id' => null, // System-level migration
            'action' => 'category_migration',
            'entity_type' => 'claim_category',
            'entity_id' => $newCategoryId,
            'old_values' => json_encode([
                'category_id' => $oldCategoryId,
                'category_name' => $oldCategory->display_name ?? 'Unknown',
            ]),
            'new_values' => json_encode([
                'category_id' => $newCategoryId,
                'category_name' => $newCategory->display_name ?? 'Unknown',
                'affected_claims_count' => $affectedClaims,
                'migration_type' => '8_to_10_category_structure'
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Database Migration System',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};