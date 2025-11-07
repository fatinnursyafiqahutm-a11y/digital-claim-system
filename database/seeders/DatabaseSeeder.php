<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ClaimCategorySeeder::class,
            PermissionSeeder::class,
            SettingSeeder::class,
        ]);

        // Optional: Create test users for development
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test Employee',
        //     'email' => 'employee@test.com',
        //     'role_id' => 1, // employee role
        // ]);
        // User::factory()->create([
        //     'name' => 'Test Finance Admin',
        //     'email' => 'admin@test.com',
        //     'role_id' => 2, // finance_admin role
        // ]);
    }
}
