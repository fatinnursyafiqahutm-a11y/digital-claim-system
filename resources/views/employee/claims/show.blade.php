<x-app-layout>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Claim Details</h2>
                        <p class="mt-1 text-sm text-gray-600">Claim ID: #{{ $claim->id }}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('employee.claims.print', $claim->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-indigo-600 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-print w-4 h-4 mr-2"></i>
                            Print / Export
                        </a>
                        <a href="{{ route('employee.claims.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6" />

                            </svg>
                            Back
                        </a>
                        @if ($claim->canBeEdited())
                            <a href="{{ route('employee.claims.edit', $claim->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Claim Summary -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm font-medium text-gray-600">Title:</dt>
                                <dd class="text-sm text-gray-900">{{ $claim->title }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm font-medium text-gray-600">Category:</dt>
                                <dd class="text-sm text-gray-900">{{ $claim->category->name }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm font-medium text-gray-600">Amount:</dt>
                                <dd class="text-sm font-medium text-gray-900">RM {{ number_format($claim->amount, 2) }}</dd>
                            </div>
                            @if ($claim->approved_amount && $claim->approved_amount != $claim->amount)
                                <div class="flex justify-between py-2 border-b border-100">
                                    <dt class="text-sm font-medium text-gray-600">Approved Amount:</dt>
                                    <dd class="text-sm font-medium text-green-600">RM {{ number_format($claim->approved_amount, 2) }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between py-2 border-b border-100">
                                <dt class="text-sm font-medium text-gray-600">Claim Date:</dt>
                                <dd class="text-sm text-gray-900">{{ $claim->claim_date->format('F d, Y') }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm font-medium text-gray-600">Status:</dt>
                                <dd>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $claim->formatted_status['class'] }}">
                                        {{ $claim->formatted_status['text'] }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between py-2">
                                <dt class="text-sm font-medium text-gray-600">Priority:</dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst($claim->priority ?? 'Normal') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                        <dl class="space-y-3">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 rounded-full bg-blue-500 mt-1"></div>
                                </div>
                                <div class="ml-3">
                                    <dt class="text-sm font-medium text-gray-900">Created</dt>
                                    <dd class="text-sm text-gray-600">{{ $claim->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                            </div>
                            @if ($claim->submitted_at)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 rounded-full bg-yellow-500 mt-1"></div>
                                    </div>
                                    <div class="ml-3">
                                        <dt class="text-sm font-medium text-gray-900">Submitted</dt>
                                        <dd class="text-sm text-gray-600">{{ $claim->submitted_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                </div>
                            @endif
                            @if ($claim->approved_at)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 rounded-full bg-green-500 mt-1"></div>
                                    </div>
                                    <div class="ml-3">
                                        <dt class="text-sm font-medium text-gray-900">Approved</dt>
                                        <dd class="text-sm text-gray-600">{{ $claim->approved_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                </div>
                            @endif
                            @if ($claim->rejected_at)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 rounded-full bg-red-500 mt-1"></div>
                                    </div>
                                    <div class="ml-3">
                                        <dt class="text-sm font-medium text-gray-900">Rejected</dt>
                                        <dd class="text-sm text-gray-600">{{ $claim->rejected_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $claim->description }}</p>
                    </div>
                </div>

                <!-- Category Specific Data -->
                @if (!empty($categoryData))
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Additional Information</h3>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            @foreach ($categoryData as $key => $value)
                                <div class="mb-2">
                                    <dt class="text-sm font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</dt>
                                    <dd class="text-sm text-gray-900">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Admin Notes -->
                @if ($claim->admin_notes)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Admin Notes</h3>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-gray-700">{{ $claim->admin_notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- Rejection Reason -->
                @if ($claim->rejection_reason)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Rejection Reason</h3>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <p class="text-gray-700">{{ $claim->rejection_reason }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Receipts -->
        @if ($claim->receipts->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Supporting Documents</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($claim->receipts as $receipt)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">{{ $receipt->original_filename }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Size: {{ $receipt->getFormattedFileSize() }} |
                                            Type: {{ $receipt->getFileExtension() }}
                                            @if ($receipt->is_primary)
                                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Primary
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        <a href="{{ $receipt->getDownloadUrl() }}" class="text-indigo-600 hover:text-indigo-900">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                @if ($receipt->canBePreviewed())
                                    <div class="mt-3">
                                        <img src="{{ $receipt->getPreviewUrl() }}" alt="{{ $receipt->original_filename }}"
                                             class="w-full h-32 object-cover rounded cursor-pointer hover:opacity-80 transition-opacity"
                                             onclick="window.open('{{ $receipt->getPreviewUrl() }}', '_blank')">
                                    </div>
                                @else
                                    <div class="mt-3 bg-gray-100 p-4 rounded text-center">
                                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="mt-2 text-xs text-gray-500">Document Preview Not Available</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Audit Trail -->
        @if ($claim->auditLogs->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Activity Log</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach ($claim->auditLogs->sortByDesc('created_at') as $log)
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    @if ($log->action == 'claim_created')
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                    @elseif ($log->action == 'claim_submitted')
                                        <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    @elseif ($log->action == 'claim_approved')
                                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    @elseif ($log->action == 'claim_rejected')
                                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 font-medium">{{ $log->description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $log->created_at->format('M d, Y H:i') }} |
                                        {{ $log->user ? $log->user->name : 'System' }}
                                    </p>
                                    @if ($log->details)
                                        <div class="mt-2 text-xs text-gray-600 bg-white p-2 rounded">
                                            <pre>{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Actions -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between">
                    <div>
                        @if ($claim->canBeSubmitted())
                            <form method="POST" action="{{ route('employee.claims.submit', $claim->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to submit this claim for approval? Once submitted, you cannot make changes.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2 2zm0 0v-8" />
                                    </svg>
                                    Submit for Approval
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="flex space-x-3">
                        @if ($claim->canBeEdited())
                            <a href="{{ route('employee.claims.edit', $claim->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Claim
                            </a>
                        @endif
                        @if ($claim->canBeEdited())
                            <form method="POST" action="{{ route('employee.claims.destroy', $claim->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this claim? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-2-1.99L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete Claim
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>