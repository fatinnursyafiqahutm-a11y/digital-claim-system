<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Notifications\UserAccountCreatedNotification;
use App\Services\UserCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    protected $userCreationService;

    public function __construct(UserCreationService $userCreationService)
    {
        $this->userCreationService = $userCreationService;
    }

    /**
     * Display a listing of all employees.
     */
    public function index(Request $request)
    {
        $query = User::with('role')
            ->whereHas('role', function ($q) {
                $q->where('name', 'employee');
            });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhere('employee_id', 'ILIKE', "%{$search}%")
                  ->orWhere('department', 'ILIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $employees = $query->latest()->paginate(10);

        return view('admin.users.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'nric' => ['required', 'string', 'max:20', 'unique:users,nric'],
            'department' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $result = $this->userCreationService->createEmployee($request->all());

        if ($result['success']) {
            $user = $result['user'];
            $password = $result['password'];

            // Send notification email to the new user
            try {
                $user->notify(new UserAccountCreatedNotification($user, $password));
            } catch (\Exception $e) {
                // Log error but don't fail the user creation
                \Illuminate\Support\Facades\Log::error('Failed to send user creation email: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Employee '{$user->name}' (ID: {$result['employee_id']}) created successfully. Welcome email sent to {$user->email}.");
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(User $user)
    {
        // Ensure we can only edit employees
        if (!$user->isEmployee()) {
            abort(404);
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, User $user)
    {
        // Ensure we can only edit employees
        if (!$user->isEmployee()) {
            abort(404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'department' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', $user->is_active),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Employee '{$user->name}' updated successfully.");
    }

    /**
     * Remove the specified employee from storage (deactivate).
     */
    public function destroy(User $user)
    {
        // Ensure we can only deactivate employees
        if (!$user->isEmployee()) {
            abort(404);
        }

        $user->update(['is_active' => false]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Employee '{$user->name}' has been deactivated.");
    }

    /**
     * Reset password for the specified employee.
     */
    public function resetPassword(User $user)
    {
        // Ensure we can only reset employee passwords
        if (!$user->isEmployee()) {
            abort(404);
        }

        $newPassword = $this->userCreationService->generateSecurePassword();

        $user->update([
            'password' => Hash::make($newPassword),
            'password_reset_required' => true,
        ]);

        return redirect()
            ->back()
            ->with('success', "Password reset for '{$user->name}'. New password: {$newPassword}");
    }

    /**
     * Activate the specified employee.
     */
    public function activate(User $user)
    {
        // Ensure we can only activate employees
        if (!$user->isEmployee()) {
            abort(404);
        }

        $user->update(['is_active' => true]);

        return redirect()
            ->back()
            ->with('success', "Employee '{$user->name}' has been activated.");
    }

    /**
     * Generate email from employee name via AJAX.
     */
    public function generateEmail(Request $request)
    {
        $name = $request->get('name');

        if (empty($name)) {
            return response()->json(['email' => '']);
        }

        $email = $this->userCreationService->generateEmailFromName($name);

        return response()->json(['email' => $email]);
    }
}