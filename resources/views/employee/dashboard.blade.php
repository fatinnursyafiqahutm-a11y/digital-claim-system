<x-app-layout>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Employee Dashboard</h2>
                        <p class="mt-1 text-sm text-gray-600">Welcome back, {{ $user->name }}!</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Employee Role
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">My Claims</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $statistics['total_claims'] }}</p>
                        <p class="text-sm text-blue-700 mt-1">Total claims submitted</p>
                    </div>

                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-2">Pending</h3>
                        <p class="text-3xl font-bold text-yellow-600">{{$statistics['submitted_claims'] }}</p>
                        <p class="text-sm text-yellow-700 mt-1">Awaiting approval</p>
                    </div>

                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-900 mb-2">Approved</h3>
                        <p class="text-3xl font-bold text-green-600">{{$statistics['approved_claims']}}</p>
                        <p class="text-sm text-green-700 mt-1">Claims approved</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="flex space-x-4">
                        <a href="{{ route('employee.claims.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 transition">
                            Submit New Claim
                        </a>
                        <a href="{{ route('employee.claims.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 transition">
                            View My Claims
                        </a>
                    </div>
                </div>

                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">User Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Employee ID:</span>
                            <span class="ml-2 text-gray-600">{{ $user->employee_id }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Department:</span>
                            <span class="ml-2 text-gray-600">{{ $user->department }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Email:</span>
                            <span class="ml-2 text-gray-600">{{ $user->email }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Status:</span>
                            <span class="ml-2 text-green-600">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>