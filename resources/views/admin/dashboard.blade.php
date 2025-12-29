<x-app-layout>
    <div class="dashboard-card mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Finance Admin Dashboard</h2>
                <p class="text-gray-600">Welcome back, {{ $user->name }}!</p>
            </div>
            <div>
                <span class="badge badge-info">
                    Finance Admin
                </span>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-blue-900">Total Claims</h3>
                <i class="fas fa-file-invoice-dollar text-blue-600 text-2xl"></i>
            </div>
            <p class="stat-value text-blue-600">{{ $statistics['total_claims'] ?? 0 }}</p>
            <p class="stat-label text-blue-700">All claims in system</p>
        </div>

        <div class="stat-card bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-yellow-900">Pending Review</h3>
                <i class="fas fa-clock text-yellow-600 text-2xl"></i>
            </div>
            <p class="stat-value text-yellow-600">{{ $pendingCount ?? 0 }}</p>
            <p class="stat-label text-yellow-700">Awaiting your review</p>
        </div>

        <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 border border-green-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-green-900">Approved</h3>
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
            <p class="stat-value text-green-600">{{ $statistics['approved_claims'] ?? 0 }}</p>
            <p class="stat-label text-green-700">Approved claims</p>
        </div>

        <div class="stat-card bg-gradient-to-br from-red-50 to-red-100 border border-red-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-red-900">Rejected</h3>
                <i class="fas fa-times-circle text-red-600 text-2xl"></i>
            </div>
            <p class="stat-value text-red-600">{{ $statistics['rejected_claims'] ?? 0 }}</p>
            <p class="stat-label text-red-700">Rejected claims</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-card mb-8">
        <h3 class="heading-2">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary text-center">
                <i class="fas fa-user-plus mr-2"></i>
                Add New Employee
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-alt text-center">
                <i class="fas fa-users mr-2"></i>
                Manage Employees
            </a>
            <a href="{{ route('admin.claims.index') }}" class="btn btn-alt text-center">
                <i class="fas fa-file-invoice-dollar mr-2"></i>
                Manage Claims
            </a>
            <a href="{{ route('admin.claims.index', ['unread' => 1]) }}" class="btn btn-alt text-center relative">
                <i class="fas fa-bell mr-2"></i>
                Notifications
            </a>
        </div>
    </div>

    <!-- User Management Overview -->
    <div class="dashboard-card mb-8">
        <h3 class="heading-2">User Management Overview</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass-card p-6 bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-lg font-semibold text-blue-900">Total Employees</h4>
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-blue-600">
                    {{ \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'employee'))->count() }}
                </p>
                <p class="text-sm text-blue-700 mt-1">All registered employees</p>
            </div>

            <div class="glass-card p-6 bg-gradient-to-br from-green-50 to-green-100 border border-green-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-lg font-semibold text-green-900">Active Employees</h4>
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-green-600">
                    {{ \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'employee'))->where('is_active', true)->count() }}
                </p>
                <p class="text-sm text-green-700 mt-1">Currently active accounts</p>
            </div>

            <div class="glass-card p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-lg font-semibold text-yellow-900">Password Reset Required</h4>
                    <i class="fas fa-key text-yellow-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-yellow-600">
                    {{ \App\Models\User::where('password_reset_required', true)->count() }}
                </p>
                <p class="text-sm text-yellow-700 mt-1">Need password change</p>
            </div>
        </div>
    </div>

    <!-- Recent Claims Activity -->
    <div class="dashboard-card mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="heading-2">Recent Claim Activity</h3>
            <a href="{{ route('admin.claims.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View All Claims →
            </a>
        </div>
        @if($recentClaims->count() > 0)
            <div class="glass-card border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claim Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentClaims as $claim)
                                <tr class="hover:bg-gray-50 {{ $claim['status'] == 'Submitted' || $claim['status'] == 'Under Review' ? 'bg-yellow-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $claim['employee_name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $claim['title'] }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $claim['id'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">{{ $claim['category'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">RM {{ number_format($claim['amount'], 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $claim['status_class'] }}">
                                            {{ $claim['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $claim['submitted_at'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ $claim['claim_url'] }}" class="text-blue-600 hover:text-blue-900">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="glass-card p-8 text-center bg-gray-50 border border-gray-200">
                <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500 text-lg">No claims submitted yet.</p>
                <p class="text-gray-400 text-sm mt-2">Employee claims will appear here once submitted.</p>
            </div>
        @endif
    </div>

    <!-- Admin Information -->
    <div class="dashboard-card">
        <h3 class="heading-2">Admin Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Name</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-envelope text-white"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Email</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->email }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-shield text-white"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Role</p>
                    <p class="text-lg font-semibold text-purple-600">Finance Administrator</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-white"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Status</p>
                    <p class="text-lg font-semibold text-green-600">Active</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>