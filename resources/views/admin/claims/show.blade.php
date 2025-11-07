<x-app-layout>
    <div class="dashboard-card mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Claim Details</h2>
                <p class="text-gray-600">Review and manage claim #{{ $claim->id }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.claims.index') }}" class="btn btn-ghost">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Claims
                </a>

                @if ($claim->canBeApproved())
                    <button onclick="openApproveModal()" class="btn btn-success">
                        <i class="fas fa-check mr-2"></i>
                        Approve Claim
                    </button>
                @endif

                @if ($claim->canBeRejected())
                    <button onclick="openRejectModal()" class="btn btn-danger">
                        <i class="fas fa-times mr-2"></i>
                        Reject Claim
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Claim Information -->
            <div class="dashboard-card">
                <h3 class="heading-2 mb-6">Claim Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label">Claim Title</label>
                        <p class="text-lg font-medium text-gray-900">{{ $claim->title }}</p>
                    </div>

                    <div>
                        <label class="form-label">Category</label>
                        <p class="text-lg font-medium text-gray-900">{{ $claim->category->name }}</p>
                    </div>

                    <div>
                        <label class="form-label">Claim Amount</label>
                        <p class="text-2xl font-bold text-blue-600">RM {{ number_format($claim->amount, 2) }}</p>
                        @if ($claim->approved_amount && $claim->approved_amount != $claim->amount)
                            <p class="text-sm text-green-600 mt-1">Approved: RM {{ number_format($claim->approved_amount, 2) }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="form-label">Claim Date</label>
                        <p class="text-lg font-medium text-gray-900">{{ $claim->claim_date->format('F d, Y') }}</p>
                    </div>

                    <div>
                        <label class="form-label">Priority</label>
                        @php
                            $priorityColors = [
                                'low' => 'bg-gray-100 text-gray-800',
                                'normal' => 'bg-blue-100 text-blue-800',
                                'high' => 'bg-orange-100 text-orange-800',
                                'urgent' => 'bg-red-100 text-red-800'
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $priorityColors[$claim->priority] ?? 'bg-gray-100 text-gray-800' }}">
                            <i class="fas fa-flag mr-2"></i>{{ ucfirst($claim->priority) }}
                        </span>
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $claim->formatted_status['class'] }}">
                            {{ $claim->formatted_status['text'] }}
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="form-label">Description</label>
                    <p class="text-gray-700 leading-relaxed">{{ $claim->description }}</p>
                </div>

                <!-- Category Specific Data -->
                @if ($claim->category_data && !empty($claim->category_data))
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-3">{{ $claim->category->display_name }} Details</h4>
                        @php
                            $categoryData = is_string($claim->category_data) ? json_decode($claim->category_data, true) : $claim->category_data;
                        @endphp

                        @if ($claim->category->name === 'accommodation')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if (!empty($categoryData['hotel_name']))
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Hotel Name</label>
                                        <p class="text-gray-900">{{ $categoryData['hotel_name'] }}</p>
                                    </div>
                                @endif
                                @if (!empty($categoryData['location']))
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Location</label>
                                        <p class="text-gray-900">{{ $categoryData['location'] }}</p>
                                    </div>
                                @endif
                                @if (!empty($categoryData['check_in_date']))
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Check-in Date</label>
                                        <p class="text-gray-900">{{ \Carbon\Carbon::parse($categoryData['check_in_date'])->format('F d, Y') }}</p>
                                    </div>
                                @endif
                                @if (!empty($categoryData['check_out_date']))
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Check-out Date</label>
                                        <p class="text-gray-900">{{ \Carbon\Carbon::parse($categoryData['check_out_date'])->format('F d, Y') }}</p>
                                    </div>
                                @endif
                                @if (!empty($categoryData['room_type']))
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Room Type</label>
                                        <p class="text-gray-900">{{ ucfirst($categoryData['room_type']) }}</p>
                                    </div>
                                @endif
                                @if (!empty($categoryData['booking_reference']))
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">Booking Reference</label>
                                        <p class="text-gray-900">{{ $categoryData['booking_reference'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($categoryData as $key => $value)
                                    @if (!empty($value))
                                        <div>
                                            <label class="text-sm font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                                            <p class="text-gray-900">{{ $value }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Receipts -->
            <div class="dashboard-card">
                <h3 class="heading-2 mb-6">Supporting Documents</h3>

                @if ($claim->receipts->count() > 0)
                    <div class="space-y-4">
                        @foreach ($claim->receipts as $receipt)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            @if ($receipt->canBePreviewed())
                                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-image text-blue-600"></i>
                                                </div>
                                            @else
                                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-file-pdf text-gray-600"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $receipt->original_filename }}</p>
                                            <p class="text-sm text-gray-500">{{ $receipt->getFormattedFileSize() }} • {{ $receipt->getFileExtension() }}</p>
                                            @if ($receipt->is_primary)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                                    <i class="fas fa-star mr-1"></i>Primary
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if ($receipt->canBePreviewed())
                                            <a href="{{ $receipt->getPreviewUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        <a href="{{ $receipt->getDownloadUrl() }}" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="fas fa-file-upload text-gray-400 text-4xl mb-3"></i>
                        <p class="text-gray-500">No supporting documents uploaded.</p>
                    </div>
                @endif
            </div>

            <!-- Audit Trail -->
            <div class="dashboard-card">
                <h3 class="heading-2 mb-6">Audit Trail</h3>

                @if ($claim->auditLogs->count() > 0)
                    <div class="space-y-4">
                        @foreach ($claim->auditLogs->sortBy('created_at') as $log)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    @php
                                        $iconColors = [
                                            'claim_submitted' => 'bg-blue-100 text-blue-600',
                                            'claim_approved' => 'bg-green-100 text-green-600',
                                            'claim_rejected' => 'bg-red-100 text-red-600',
                                            'claim_updated' => 'bg-yellow-100 text-yellow-600',
                                        ];
                                        $iconColor = $iconColors[$log->action] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <div class="w-8 h-8 {{ $iconColor }} rounded-full flex items-center justify-center">
                                        <i class="fas fa-history text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="font-medium text-gray-900">{{ $log->description }}</p>
                                        <span class="text-sm text-gray-500">{{ $log->created_at->format('M d, Y H:i') }}</span>
                                    </div>
                                    @if ($log->details && !empty($log->details))
                                        <div class="mt-2 text-sm text-gray-600">
                                            @foreach ($log->details as $key => $value)
                                                <span class="inline-block bg-gray-100 rounded px-2 py-1 mr-2 mb-1">
                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No audit trail available.</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Employee Information -->
            <div class="dashboard-card">
                <h3 class="heading-2 mb-6">Employee Information</h3>

                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-xl font-semibold">{{ substr($claim->user->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $claim->user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $claim->user->employee_id }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="form-label">Email</label>
                        <p class="text-gray-900">{{ $claim->user->email }}</p>
                    </div>

                    <div>
                        <label class="form-label">Department</label>
                        <p class="text-gray-900">{{ $claim->user->department ?? 'Not specified' }}</p>
                    </div>

                    <div>
                        <label class="form-label">Phone</label>
                        <p class="text-gray-900">{{ $claim->user->phone ?? 'Not specified' }}</p>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="dashboard-card">
                <h3 class="heading-2 mb-6">Claim Timeline</h3>

                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <i class="fas fa-plus text-blue-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Claim Created</p>
                            <p class="text-sm text-gray-500">{{ $claim->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if ($claim->submitted_at)
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <i class="fas fa-paper-plane text-yellow-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Submitted for Review</p>
                                <p class="text-sm text-gray-500">{{ $claim->submitted_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($claim->approved_at)
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Approved</p>
                                <p class="text-sm text-gray-500">{{ $claim->approved_at->format('M d, Y H:i') }}</p>
                                @if ($claim->approved_amount != $claim->amount)
                                    <p class="text-sm text-green-600">Amount: RM {{ number_format($claim->approved_amount, 2) }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($claim->rejected_at)
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <i class="fas fa-times text-red-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Rejected</p>
                                <p class="text-sm text-gray-500">{{ $claim->rejected_at->format('M d, Y H:i') }}</p>
                                @if ($claim->rejection_reason)
                                    <p class="text-sm text-red-600 mt-1">Reason: {{ $claim->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            @if ($claim->canBeApproved() || $claim->canBeRejected())
                <div class="dashboard-card">
                    <h3 class="heading-2 mb-6">Quick Actions</h3>

                    <div class="space-y-3">
                        @if ($claim->canBeApproved())
                            <button onclick="openApproveModal()" class="w-full btn btn-success">
                                <i class="fas fa-check mr-2"></i>
                                Approve Claim
                            </button>
                        @endif

                        @if ($claim->canBeRejected())
                            <button onclick="openRejectModal()" class="w-full btn btn-danger">
                                <i class="fas fa-times mr-2"></i>
                                Reject Claim
                            </button>
                        @endif

                        <a href="{{ route('admin.claims.index') }}" class="w-full btn btn-ghost text-center">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Claims List
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Approve Claim</h3>
                <form method="POST" action="{{ route('admin.claims.approve', $claim->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Claim Amount</label>
                        <input type="text" value="RM {{ number_format($claim->amount, 2) }}" readonly class="form-input bg-gray-50">
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="approved_amount">Approved Amount (RM)</label>
                        <input type="number"
                               name="approved_amount"
                               step="0.01"
                               min="0.01"
                               max="{{ $claim->amount }}"
                               class="form-input"
                               placeholder="Enter approved amount">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to approve full amount</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="admin_notes">Admin Notes</label>
                        <textarea name="admin_notes"
                                  rows="3"
                                  class="form-textarea"
                                  placeholder="Add any notes for the employee..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApproveModal()" class="btn btn-ghost">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check mr-2"></i>
                            Approve Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Claim</h3>
                <form method="POST" action="{{ route('admin.claims.reject', $claim->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Claim Title</label>
                        <input type="text" value="{{ $claim->title }}" readonly class="form-input bg-gray-50">
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="rejection_reason">Rejection Reason *</label>
                        <textarea name="rejection_reason"
                                  rows="4"
                                  class="form-textarea"
                                  placeholder="Please provide a reason for rejection..."
                                  required></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectModal()" class="btn btn-ghost">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times mr-2"></i>
                            Reject Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openApproveModal() {
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
        }

        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const approveModal = document.getElementById('approveModal');
            const rejectModal = document.getElementById('rejectModal');

            if (event.target === approveModal) {
                closeApproveModal();
            }
            if (event.target === rejectModal) {
                closeRejectModal();
            }
        }
    </script>
</x-app-layout>