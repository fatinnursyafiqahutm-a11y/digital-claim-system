<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'submit_claim', 'display_name' => 'Submit Claims'],
            ['name' => 'edit_own_claim', 'display_name' => 'Edit Own Claims'],
            ['name' => 'view_own_claim', 'display_name' => 'View Own Claims'],
            ['name' => 'upload_receipt', 'display_name' => 'Upload Receipts'],
            ['name' => 'approve_claim', 'display_name' => 'Approve Claims'],
            ['name' => 'reject_claim', 'display_name' => 'Reject Claims'],
            ['name' => 'view_all_claims', 'display_name' => 'View All Claims'],
            ['name' => 'generate_reports', 'display_name' => 'Generate Reports'],
            ['name' => 'manage_users', 'display_name' => 'Manage Users'],
            ['name' => 'manage_settings', 'display_name' => 'Manage Settings'],
        ];

        foreach ($permissions as $permission) {
            \DB::table('permissions')->insert($permission);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        // Employee permissions
        $employeeRole = \DB::table('roles')->where('name', 'employee')->first();
        $employeePermissions = ['submit_claim', 'edit_own_claim', 'view_own_claim', 'upload_receipt'];

        // Finance Admin permissions
        $financeAdminRole = \DB::table('roles')->where('name', 'finance_admin')->first();
        $financeAdminPermissions = ['approve_claim', 'reject_claim', 'view_all_claims', 'generate_reports'];

        foreach ($employeePermissions as $permissionName) {
            $permission = \DB::table('permissions')->where('name', $permissionName)->first();
            \DB::table('role_permissions')->insert([
                'role_id' => $employeeRole->id,
                'permission_id' => $permission->id,
            ]);
        }

        foreach ($financeAdminPermissions as $permissionName) {
            $permission = \DB::table('permissions')->where('name', $permissionName)->first();
            \DB::table('role_permissions')->insert([
                'role_id' => $financeAdminRole->id,
                'permission_id' => $permission->id,
            ]);
        }
    }
}
