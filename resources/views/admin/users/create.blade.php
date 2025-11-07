<x-app-layout>
    <div class="dashboard-card mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Create New Employee</h2>
                <p class="text-gray-600">Add a new employee to the system</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-alt">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Employees
            </a>
        </div>
    </div>

    <div class="dashboard-card">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="form-label" for="name">Full Name</label>
                    <input id="name"
                           class="form-input"
                           type="text"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="John Doe"
                           required
                           autofocus>
                    @error('name')
                        <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="form-label" for="email">Email Address</label>
                    <input id="email"
                           class="form-input"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="will be auto-generated"
                           required>
                    <p class="mt-2 text-sm text-gray-600">Email will be auto-generated from name (e.g., "Fatin Aina" → fatin.aina@cloudvision.com.my)</p>
                    @error('email')
                        <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                    @enderror
                </div>

                <!-- NRIC -->
                <div>
                    <label class="form-label" for="nric">NRIC Number</label>
                    <input id="nric"
                           class="form-input"
                           type="text"
                           name="nric"
                           value="{{ old('nric') }}"
                           placeholder="e.g., 950123-14-5678"
                           required>
                    <p class="mt-2 text-sm text-gray-600">Employee's NRIC will be used as default password</p>
                    @error('nric')
                        <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Department -->
                <div>
                    <label class="form-label" for="department">Department</label>
                    <input id="department"
                           class="form-input"
                           type="text"
                           name="department"
                           value="{{ old('department') }}"
                           placeholder="e.g., Finance, IT, HR"
                           required>
                    @error('department')
                        <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="form-label" for="phone">Phone Number (Optional)</label>
                    <input id="phone"
                           class="form-input"
                           type="tel"
                           name="phone"
                           value="{{ old('phone') }}"
                           placeholder="e.g., +60123456789">
                    @error('phone')
                        <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Account Information -->
                <div class="md:col-span-2">
                    <div class="glass-card p-6 bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-600 text-xl mt-1"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-blue-900 mb-3">Account Creation Information</h3>
                                <div class="text-sm text-blue-800 space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-hashtag text-blue-600"></i>
                                        <span><strong>Employee ID:</strong> Auto-generated (EMP1001, EMP1002, etc)</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-key text-blue-600"></i>
                                        <span><strong>Default Password:</strong> Employee's NRIC number</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-user-check text-blue-600"></i>
                                        <span><strong>Account Status:</strong> Active upon creation</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-sync text-blue-600"></i>
                                        <span><strong>Password Reset:</strong> Required on first login</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-envelope-check text-blue-600"></i>
                                        <span><strong>Email Verification:</strong> Auto-verified for admin-created accounts</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Employee
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            let userEmailEdited = false;

            // Function to generate email from name
            function generateEmailFromName(name) {
                if (!name.trim()) return '';

                // Convert to lowercase and replace spaces with dots
                let emailName = name.trim().toLowerCase();
                emailName = emailName.replace(/\s+/g, '.');
                emailName = emailName.replace(/[^a-z.]/g, ''); // Remove special characters except dots

                return emailName + '@cloudvision.com.my';
            }

            // Update email field when name changes (only if user hasn't manually edited email)
            nameInput.addEventListener('input', function() {
                if (!userEmailEdited) {
                    const generatedEmail = generateEmailFromName(this.value);
                    emailInput.value = generatedEmail;
                }
            });

            // Mark email as user-edited when user manually changes it
            emailInput.addEventListener('input', function() {
                const generatedEmail = generateEmailFromName(nameInput.value);
                if (this.value !== generatedEmail) {
                    userEmailEdited = true;
                } else {
                    userEmailEdited = false;
                }
            });

            // Also fetch from server for more complex email generation
            let timeout;
            nameInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    if (!userEmailEdited && this.value.trim()) {
                        fetch('{{ route("admin.users.generate-email") }}?name=' + encodeURIComponent(this.value))
                            .then(response => response.json())
                            .then(data => {
                                if (data.email) {
                                    emailInput.value = data.email;
                                }
                            })
                            .catch(error => {
                                console.error('Error generating email:', error);
                            });
                    }
                }, 500); // Debounce for 500ms
            });
        });
    </script>
</x-app-layout>