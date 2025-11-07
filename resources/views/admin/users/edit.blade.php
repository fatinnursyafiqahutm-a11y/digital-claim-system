<x-app-layout>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Edit Employee</h2>
                        <p class="mt-1 text-sm text-gray-600">Update information for {{ $user->name }}</p>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 transition">
                        Back to Employees
                    </a>
                </div>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Full Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div>
                            <x-input-label for="email" :value="__('Email Address')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Employee ID -->
                        <div>
                            <x-input-label for="employee_id" :value="__('Employee ID')" />
                            <x-text-input id="employee_id" class="block mt-1 w-full" type="text" name="employee_id" :value="old('employee_id', $user->employee_id)" required />
                            <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
                        </div>

                        <!-- Department -->
                        <div>
                            <x-input-label for="department" :value="__('Department')" />
                            <x-text-input id="department" class="block mt-1 w-full" type="text" name="department" :value="old('department', $user->department)" required />
                            <x-input-error :messages="$errors->get('department')" class="mt-2" />
                        </div>

                        <!-- Phone -->
                        <div>
                            <x-input-label for="phone" :value="__('Phone Number')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone', $user->phone)" placeholder="e.g., +60123456789" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <!-- Account Status -->
                        <div>
                            <x-input-label for="is_active" :value="__('Account Status')" />
                            <div class="mt-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Active Account</span>
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if ($user->is_active)
                                        Employee can currently access the system
                                    @else
                                        Employee cannot access the system
                                    @endif
                                </p>
                            </div>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>

                        <!-- Account Information -->
                        <div class="md:col-span-2">
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                <h3 class="text-sm font-medium text-gray-800 mb-2">Account Information</h3>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-700">Role:</span>
                                        <span class="ml-2 text-gray-600">{{ $user->role->display_name }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Created:</span>
                                        <span class="ml-2 text-gray-600">{{ $user->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Last Login:</span>
                                        <span class="ml-2 text-gray-600">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Password Reset Required:</span>
                                        <span class="ml-2">
                                            @if ($user->password_reset_required)
                                                <span class="text-yellow-600">Yes</span>
                                            @else
                                                <span class="text-green-600">No</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end">
                        <a href="{{ route('admin.users.index') }}" class="bg-gray-200 py-2 px-4 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 transition mr-3">
                            Cancel
                        </a>
                        <x-primary-button>
                            {{ __('Update Employee') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>