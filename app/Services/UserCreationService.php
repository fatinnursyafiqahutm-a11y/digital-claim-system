<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserCreationService
{
    /**
     * Create a new employee with NRIC as default password.
     */
    public function createEmployee(array $data): array
    {
        try {
            $employeeRole = Role::where('name', 'employee')->first();

            if (!$employeeRole) {
                return [
                    'success' => false,
                    'message' => 'Employee role not found in the system.',
                ];
            }

            // Use NRIC as default password
            $password = $data['nric'];

            // Generate auto employee ID
            $employeeId = $this->generateEmployeeId();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'employee_id' => $employeeId,
                'department' => $data['department'],
                'phone' => $data['phone'] ?? null,
                'nric' => $data['nric'],
                'password' => Hash::make($password),
                'role_id' => $employeeRole->id,
                'is_active' => true,
                'password_reset_required' => true,
                'email_verified_at' => now(), // Auto-verify for admin-created accounts
            ]);

            Log::info('Employee created by admin', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'employee_name' => $user->name,
                'employee_email' => $user->email,
                'employee_id' => $employeeId,
            ]);

            return [
                'success' => true,
                'user' => $user,
                'password' => $password,
                'employee_id' => $employeeId,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create employee', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create employee: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a secure temporary password.
     */
    public function generateSecurePassword(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $password = '';

        // Ensure at least one uppercase letter
        $password .= $this->getRandomChar('ABCDEFGHJKLMNPQRSTUVWXYZ');

        // Ensure at least one lowercase letter
        $password .= $this->getRandomChar('abcdefghjkmnpqrstuvwxyz');

        // Ensure at least one number
        $password .= $this->getRandomChar('23456789');

        // Add remaining characters
        for ($i = 0; $i < 9; $i++) { // Total 12 characters
            $password .= $this->getRandomChar($characters);
        }

        return str_shuffle($password);
    }

    /**
     * Get a random character from a string.
     */
    private function getRandomChar(string $characters): string
    {
        return $characters[random_int(0, strlen($characters) - 1)];
    }

    /**
     * Generate auto-increment employee ID (EMP1001, EMP1002, etc).
     */
    public function generateEmployeeId(): string
    {
        // Get the highest existing employee ID
        $lastEmployee = User::where('employee_id', 'like', 'EMP%')
            ->orderBy('employee_id', 'desc')
            ->first();

        if ($lastEmployee && preg_match('/EMP(\d+)/', $lastEmployee->employee_id, $matches)) {
            $lastNumber = (int) $matches[1];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1001; // Start from EMP1001
        }

        return 'EMP' . $newNumber;
    }

    /**
     * Generate email from employee name.
     */
    public function generateEmailFromName(string $name): string
    {
        // Convert name to lowercase and replace spaces with dots
        $emailName = strtolower(trim($name));
        $emailName = preg_replace('/\s+/', '.', $emailName);
        $emailName = preg_replace('/[^a-z.]/', '', $emailName); // Remove special characters except dots

        return $emailName . '@cloudvision.com.my';
    }
}