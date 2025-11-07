<x-app-layout>
    <div class="dashboard-card mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Employee Management</h2>
                <p class="text-gray-600">Manage employee accounts and credentials</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus mr-2"></i>
                Add New Employee
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="dashboard-card mb-8">
        <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label" for="search">Search Employees</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by name, email, employee ID, or department..."
                               class="form-input pl-10">
                    </div>
                </div>
                <div>
                    <label class="form-label" for="status">Status Filter</label>
                    <select name="status" class="form-input">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="btn btn-primary flex-1">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">
                            <i class="fas fa-times mr-2"></i>
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Employees Table -->
    <div class="dashboard-card">
        @forelse ($employees as $employee)
            <div class="overflow-x-auto">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Employee ID</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $employee->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $employee->email }}</div>
                                        @if ($employee->phone)
                                            <div class="text-xs text-gray-400">
                                                <i class="fas fa-phone mr-1"></i>{{ $employee->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-id-badge text-gray-400"></i>
                                    <span class="font-mono text-sm">{{ $employee->employee_id }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-building text-gray-400"></i>
                                    <span>{{ $employee->department }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    @if ($employee->is_active)
                                        <span class="badge badge-approved">Active</span>
                                    @else
                                        <span class="badge badge-rejected">Inactive</span>
                                    @endif
                                    @if ($employee->password_reset_required)
                                        <span class="badge badge-pending">Reset Required</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-clock text-gray-400"></i>
                                    <span class="text-sm">
                                        {{ $employee->last_login_at ? $employee->last_login_at->diffForHumans() : 'Never' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.users.edit', $employee) }}"
                                       class="btn btn-sm btn-alt"
                                       title="Edit Employee">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if ($employee->is_active)
                                        <form method="POST" action="{{ route('admin.users.reset-password', $employee) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-ghost"
                                                    title="Reset Password"
                                                    onclick="return confirm('Are you sure you want to reset the password for {{ $employee->name }}?')">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.destroy', $employee) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="Deactivate Employee"
                                                    onclick="return confirm('Are you sure you want to deactivate {{ $employee->name }}?')">
                                                <i class="fas fa-user-times"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.activate', $employee) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-alt"
                                                    title="Activate Employee">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($employees->hasPages())
                <div class="mt-6 flex justify-center">
                    {{ $employees->links() }}
                </div>
            @endif
        @empty
            <div class="text-center py-12">
                <i class="fas fa-users text-gray-300 text-6xl mb-6"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Employees Found</h3>
                <p class="text-gray-500 mb-6">Get started by adding your first employee to the system.</p>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus mr-2"></i>
                    Add First Employee
                </a>
            </div>
        @endforelse
    </div>
</x-app-layout>