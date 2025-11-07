<x-app-layout>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">My Claims</h2>
                        <p class="mt-1 text-sm text-gray-600">Manage and track your expense claims</p>
                    </div>
                    <a href="{{ route('employee.claims.create') }}" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Claim
                    </a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Total Claims</p>
                                <p class="text-2xl font-bold text-blue-900">{{ $statistics['total_claims'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-600">Pending</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ $statistics['submitted_claims'] + $statistics['under_review_claims'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-600">Approved</p>
                                <p class="text-2xl font-bold text-green-900">{{ $statistics['approved_claims'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-600">Rejected</p>
                                <p class="text-2xl font-bold text-red-900">{{ $statistics['rejected_claims'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('employee.claims.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="under_review">Under Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <div class="mt-1 relative">
                                <input type="text" id="search" name="search" value="{{ request('search') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
                                    placeholder="Search by title or description...">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="flex space-x-3">
                            <button type="submit" class="btn btn-ghost">
                                Filter
                            </button>
                            <a href="{{ route('employee.claims.index') }}" class="btn btn-ghost">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Claims List -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-2">
                @if ($claims->count() > 0)
                    <div class="overflow-x-auto -mx-2 px-2">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Claim Details</th>
                                    <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Category</th>
                                    <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Amount</th>
                                    <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Date</th>
                                    <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Status</th>
                                    <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($claims as $claim)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-8 py-4 whitespace-nowrap text-center">
                                            <div class="text-left inline-block">
                                                <div class="text-sm font-medium text-gray-900">{{ $claim->title }}</div>
                                                <div class="text-sm text-gray-500">{{ Str::limit($claim->description, 50) }}</div>
                                                <div class="text-xs text-gray-400 mt-1">ID: {{ $claim->id }}</div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-gray-900">{{ $claim->category->name }}</span>
                                        </td>
                                        <td class="px-8 py-4 whitespace-nowrap text-center">
                                            <div class="text-sm font-medium text-gray-900">RM {{ number_format($claim->amount, 2) }}</div>
                                            @if ($claim->approved_amount && $claim->approved_amount != $claim->amount)
                                                <div class="text-xs text-green-600">Approved: RM {{ number_format($claim->approved_amount, 2) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ $claim->claim_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-8 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $claim->formatted_status['class'] }}">
                                                {{ $claim->formatted_status['text'] }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-4 whitespace-nowrap text-sm font-medium text-center">
                                            <div class="flex flex-col space-y-1">
                                                <a href="{{ route('employee.claims.show', $claim->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    View
                                                </a>
                                                @if ($claim->canBeEdited())
                                                    <a href="{{ route('employee.claims.edit', $claim->id) }}" class="text-yellow-600 hover:text-yellow-900">
                                                        Edit
                                                    </a>
                                                @endif
                                                @if ($claim->canBeSubmitted())
                                                    <form method="POST" action="{{ route('employee.claims.submit', $claim->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to submit this claim for approval?');">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900">
                                                            Submit
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($claim->canBeEdited())
                                                    <form method="POST" action="{{ route('employee.claims.destroy', $claim->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this claim?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $claims->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No claims found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first claim.</p>
                        <div class="mt-6">
                            <a href="{{ route('employee.claims.create') }}" class="btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                New Claim
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>