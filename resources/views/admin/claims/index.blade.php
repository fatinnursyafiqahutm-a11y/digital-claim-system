<x-app-layout>
    <div class="dashboard-card mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Claims Management</h2>
                <p class="text-gray-600">Review and manage employee expense claims</p>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-blue-900">Total Claims</h3>
                <i class="fas fa-file-invoice-dollar text-blue-600 text-2xl"></i>
            </div>
            <p class="stat-value text-blue-600">{{ $statistics['total_claims'] }}</p>
            <p class="stat-label text-blue-700">All claims in system</p>
        </div>

        <div class="stat-card bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-yellow-900">Pending Review</h3>
                <i class="fas fa-clock text-yellow-600 text-2xl"></i>
            </div>
            <p class="stat-value text-yellow-600">{{ $statistics['submitted_claims'] + $statistics['under_review_claims'] }}</p>
            <p class="stat-label text-yellow-700">Awaiting your review</p>
        </div>

        <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 border border-green-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-green-900">Approved</h3>
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
            <p class="stat-value text-green-600">{{ $statistics['approved_claims'] }}</p>
            <p class="stat-label text-green-700">Approved claims</p>
        </div>

        <div class="stat-card bg-gradient-to-br from-red-50 to-red-100 border border-red-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-red-900">Rejected</h3>
                <i class="fas fa-times-circle text-red-600 text-2xl"></i>
            </div>
            <p class="stat-value text-red-600">{{ $statistics['rejected_claims'] }}</p>
            <p class="stat-label text-red-700">Rejected claims</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="dashboard-card mb-8">
        <form method="GET" action="{{ route('admin.claims.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label" for="search">Search Claims</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by title, employee name, or ID..."
                               class="form-input pl-10">
                    </div>
                </div>
                <div>
                    <label class="form-label" for="status">Status</label>
                    <select name="status" class="form-input">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="category_id">Category</label>
                    <select name="category_id" class="form-input">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="btn btn-primary flex-1">
                        <i class="fas fa-search mr-2"></i>
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'status', 'category_id']))
                        <a href="{{ route('admin.claims.index') }}" class="btn btn-ghost">
                            <i class="fas fa-times mr-2"></i>
                            Clear
                        </a>
                    @endif
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label" for="date_from">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label" for="date_to">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label" for="amount_range">Amount Range</label>
                    <select name="amount_range" class="form-input">
                        <option value="">All Amounts</option>
                        <option value="0-100" {{ request('amount_range') == '0-100' ? 'selected' : '' }}>RM 0 - 100</option>
                        <option value="100-500" {{ request('amount_range') == '100-500' ? 'selected' : '' }}>RM 100 - 500</option>
                        <option value="500-1000" {{ request('amount_range') == '500-1000' ? 'selected' : '' }}>RM 500 - 1,000</option>
                        <option value="1000-5000" {{ request('amount_range') == '1000-5000' ? 'selected' : '' }}>RM 1,000 - 5,000</option>
                        <option value="5000+" {{ request('amount_range') == '5000+' ? 'selected' : '' }}>RM 5,000+</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Claims List -->
    <div class="dashboard-card">
        @if ($claims->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/6">Claim Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/8">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/8">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/8">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($claims as $claim)
                            <tr class="hover:bg-gray-50 {{ $claim->status == 'submitted' || $claim->status == 'under_review' ? 'bg-yellow-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $claim->title }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($claim->description, 50) }}</div>
                                        <div class="text-xs text-gray-400 mt-1">ID: {{ $claim->id }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-white text-xs font-semibold">{{ substr($claim->user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $claim->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $claim->user->employee_id ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $claim->category->display_name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">RM {{ number_format($claim->amount, 2) }}</div>
                                    @if ($claim->approved_amount && $claim->approved_amount != $claim->amount)
                                        <div class="text-xs text-green-600">Approved: RM {{ number_format($claim->approved_amount, 2) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $claim->claim_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $claim->formatted_status['class'] }}">
                                        {{ $claim->formatted_status['text'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex flex-col space-y-1">
                                        <a href="{{ route('admin.claims.show', $claim->id) }}" class="text-blue-600 hover:text-blue-900">
                                            View
                                        </a>

                                        @if ($claim->canBeApproved())
                                            <button onclick="openApproveModal({{ $claim->id }}, '{{ $claim->title }}', {{ $claim->amount }})" class="text-green-600 hover:text-green-900">
                                                Approve
                                            </button>
                                        @endif

                                        @if ($claim->canBeRejected())
                                            <button onclick="openRejectModal({{ $claim->id }}, '{{ $claim->title }}')" class="text-red-600 hover:text-red-900">
                                                Reject
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Showing {{ $claims->firstItem() }} to {{ $claims->lastItem() }} of {{ $claims->total() }} claims
                </div>
                <div class="flex space-x-1">
                    {{ $claims->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No claims found</h3>
                <p class="text-gray-500">No claims match your current filters.</p>
                @if (request()->hasAny(['search', 'status', 'category_id', 'date_from', 'date_to', 'amount_range']))
                    <a href="{{ route('admin.claims.index') }}" class="btn btn-primary mt-4">
                        <i class="fas fa-times mr-2"></i>
                        Clear Filters
                    </a>
                @else
                    <p class="text-sm text-gray-400 mt-2">Employee claims will appear here once submitted.</p>
                @endif
            </div>
        @endif
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Approve Claim</h3>
                <form id="approveForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="claim_id" id="approve_claim_id">

                    <div class="mb-4">
                        <label class="form-label">Claim Title</label>
                        <input type="text" id="approve_claim_title" readonly class="form-input bg-gray-50">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Claim Amount</label>
                        <input type="text" id="approve_claim_amount" readonly class="form-input bg-gray-50">
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="approved_amount">Approved Amount (RM)</label>
                        <input type="number"
                               id="approved_amount"
                               name="approved_amount"
                               step="0.01"
                               min="0.01"
                               class="form-input"
                               placeholder="Enter approved amount">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to approve full amount</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="admin_notes">Admin Notes</label>
                        <textarea id="admin_notes"
                                  name="admin_notes"
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
                <form id="rejectForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="claim_id" id="reject_claim_id">

                    <div class="mb-4">
                        <label class="form-label">Claim Title</label>
                        <input type="text" id="reject_claim_title" readonly class="form-input bg-gray-50">
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="rejection_reason">Rejection Reason *</label>
                        <textarea id="rejection_reason"
                                  name="rejection_reason"
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
        function openApproveModal(claimId, claimTitle, claimAmount) {
            document.getElementById('approve_claim_id').value = claimId;
            document.getElementById('approve_claim_title').value = claimTitle;
            document.getElementById('approve_claim_amount').value = 'RM ' + claimAmount.toFixed(2);
            document.getElementById('approveForm').action = '/admin/claims/' + claimId + '/approve';
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
            document.getElementById('approved_amount').value = '';
            document.getElementById('admin_notes').value = '';
        }

        function openRejectModal(claimId, claimTitle) {
            document.getElementById('reject_claim_id').value = claimId;
            document.getElementById('reject_claim_title').value = claimTitle;
            document.getElementById('rejectForm').action = '/admin/claims/' + claimId + '/reject';
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejection_reason').value = '';
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