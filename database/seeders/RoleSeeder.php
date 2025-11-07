<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'employee',
                'display_name' => 'Employee',
                'description' => 'Regular employee who can submit claims and track their status',
            ],
            [
                'name' => 'finance_admin',
                'display_name' => 'Finance Admin',
                'description' => 'Finance administrator who can review, approve/reject claims and generate reports',
            ],
        ];

        foreach ($roles as $role) {
            \DB::table('roles')->insert($role);
        }
    }
}
