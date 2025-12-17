<x-app-layout>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Submit New Claim</h2>
                        <p class="mt-1 text-sm text-gray-600">Fill in the details below to submit a new expense claim</p>
                    </div>
                    <div class="text-right">
                        <a href="{{ route('employee.claims.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Back to Claims
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    There were {{ $errors->count() }} error(s) with your submission:
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('employee.claims.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Claim Title *</label>
                                <input type="text" id="title" name="title" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="e.g., Business Lunch with Client"
                                    value="{{ old('title') }}">
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Claim Category *</label>
                                <select id="category_id" name="category_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select a category...</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->display_name }} (Max: RM {{ number_format($category->getMaxAmount(), 2) }})</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Select the appropriate category for your expense</p>
                            </div>

                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Amount (RM) *</label>
                                <input type="number" id="amount" name="amount" required step="0.01" min="0.01" max="5000"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="0.00"
                                    value="{{ old('amount') }}">
                                <p class="mt-1 text-xs text-gray-500">Maximum amount: RM 5,000.00</p>
                            </div>

                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700">Currency *</label>
                                <select id="currency" name="currency" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="MYR" {{ old('currency') == 'MYR' ? 'selected' : '' }}>MYR - Malaysian Ringgit</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">All claims must be in Malaysian Ringgit</p>
                            </div>

                            <div>
                                <label for="claim_date" class="block text-sm font-medium text-gray-700">Claim Date *</label>
                                <input type="date" id="claim_date" name="claim_date" required
                                    max="{{ now()->format('Y-m-d') }}"
                                    min="{{ now()->subDays(30)->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('claim_date') }}">
                                <p class="mt-1 text-xs text-gray-500">Within last 30 days only</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                            <textarea id="description" name="description" rows="3" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Provide detailed description of the expense...">{{ old('description') }}</textarea>
                        </div>

                        <div class="mt-4">
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select id="priority" name="priority"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="normal" {{ old('priority') == 'normal' || !old('priority') ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category Specific Fields -->
                    <div id="category-fields" class="bg-blue-50 p-6 rounded-lg hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Category Specific Information</h3>
                        <div id="category-fields-content">
                            <!-- Dynamic fields will be loaded here -->
                        </div>
                    </div>

                    <!-- Receipt Upload -->
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Supporting Documents</h3>

                        <div class="mb-4">
                            <label for="receipts" class="block text-sm font-medium text-gray-700">Upload Receipts *</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="receipts" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload files</span>
                                            <input id="receipts" name="receipts[]" type="file" class="sr-only" multiple accept=".jpg,.jpeg,.png,.pdf">
                                        </label>
                                        <p class="pl-1"> or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB each (max 5 files)</p>
                                </div>
                                <div id="file-list" class="mt-4 space-y-2"></div>
                            </div>
                        </div>

                        <div id="receipt-preview" class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 hidden">
                            <!-- Uploaded files will be displayed here -->
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('employee.claims.index') }}" class="btn btn-ghost">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Submit Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Pass old form data to JavaScript for category-specific fields
        window.oldFormData = @json(old());

        // Handle file upload preview
        document.getElementById('receipts').addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            const preview = document.getElementById('receipt-preview');
            const fileList = document.getElementById('file-list');

            // Clear existing previews
            preview.innerHTML = '';
            fileList.innerHTML = '';

            files.forEach((file, index) => {
                // Add to file list
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-2 bg-white rounded border';
                fileItem.innerHTML = `
                    <span class="text-sm text-gray-700">${file.name}</span>
                    <span class="text-xs text-gray-500">${(file.size / 1024).toFixed(2)} KB</span>
                `;
                fileList.appendChild(fileItem);

                // Add preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'relative group';

                    if (file.type.startsWith('image/')) {
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="${file.name}" class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                <p class="text-white text-xs text-center px-2">${file.name}</p>
                            </div>
                        `;
                    } else {
                        previewItem.innerHTML = `
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">${file.name}</p>
                            </div>
                        `;
                    }

                    preview.appendChild(previewItem);
                };

                if (file.type.startsWith('image/')) {
                    reader.readAsDataURL(file);
                } else {
                    reader.onload = function() {
                        reader.onload(null); // Use the onload handler above
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Handle category-specific fields
        document.getElementById('category_id').addEventListener('change', function(e) {
            const categoryFields = document.getElementById('category-fields');
            const categoryFieldsContent = document.getElementById('category-fields-content');
            const categoryId = e.target.value;

            // Clear existing fields
            categoryFieldsContent.innerHTML = '';

            if (categoryId) {
                // Show loading state
                categoryFields.classList.remove('hidden');
                categoryFieldsContent.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div><p class="mt-2 text-sm text-gray-600">Loading category fields...</p></div>';

                // Small delay to show loading state, then render fields
                setTimeout(() => {

                // Add category-specific fields based on selection
                let fieldsHTML = '';

                // New 10-category structure (IDs 19-28)
                if (categoryId == '19') { // Entertainment
                    fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name *</label>
                                <input type="text" id="customer_name" name="category_data[customer_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter customer name"
                                    value="${window.oldFormData['category_data']?.customer_name || ''}">
                            </div>
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name *</label>
                                <input type="text" id="company_name" name="category_data[company_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter company name"
                                    value="${window.oldFormData['category_data']?.company_name || ''}">
                            </div>
                        </div>
                    `;
                } else if (categoryId == '20') { // Accommodation
                    fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="accommodation_name" class="block text-sm font-medium text-gray-700">Accommodation Name *</label>
                                <input type="text" id="accommodation_name" name="category_data[accommodation_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter accommodation name"
                                    value="${(window.oldFormData.category_data && window.oldFormData.category_data.accommodation_name) ? window.oldFormData.category_data.accommodation_name : ''}">
                            </div>
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name *</label>
                                <input type="text" id="company_name" name="category_data[company_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter company name"
                                    value="${(window.oldFormData.category_data && window.oldFormData.category_data.company_name) ? window.oldFormData.category_data.company_name : ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700">Date From *</label>
                                <input type="date" id="date_from" name="category_data[date_from]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${(window.oldFormData.category_data && window.oldFormData.category_data.date_from) ? window.oldFormData.category_data.date_from : ''}">
                            </div>
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700">Date To *</label>
                                <input type="date" id="date_to" name="category_data[date_to]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${(window.oldFormData.category_data && window.oldFormData.category_data.date_to) ? window.oldFormData.category_data.date_to : ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="country" class="block text-sm font-medium text-gray-700">Country *</label>
                            <input type="text" id="country" name="category_data[country]"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Enter country"
                                value="${(window.oldFormData.category_data && window.oldFormData.category_data.country) ? window.oldFormData.category_data.country : ''}">
                        </div>
                    `;
                } else if (categoryId == '21') { // Transportation
                    fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="transportation_type" class="block text-sm font-medium text-gray-700">Transportation Type *</label>
                                <select id="transportation_type" name="category_data[transportation_type]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select type...</option>
                                    <option value="grab" ${window.oldFormData['category_data']?.transportation_type == 'grab' ? 'selected' : ''}>Grab</option>
                                    <option value="flight" ${window.oldFormData['category_data']?.transportation_type == 'flight' ? 'selected' : ''}>Flight</option>
                                </select>
                            </div>
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date *</label>
                                <input type="date" id="date" name="category_data[date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.date || ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="destination_from" class="block text-sm font-medium text-gray-700">Destination From *</label>
                                <input type="text" id="destination_from" name="category_data[destination_from]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter departure location"
                                    value="${window.oldFormData['category_data']?.destination_from || ''}">
                            </div>
                            <div>
                                <label for="destination_to" class="block text-sm font-medium text-gray-700">Destination To *</label>
                                <input type="text" id="destination_to" name="category_data[destination_to]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter destination"
                                    value="${window.oldFormData['category_data']?.destination_to || ''}">
                            </div>
                        </div>
                    `;
                } else if (categoryId == '22') { // Petrol
                    fieldsHTML = `
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Date *</label>
                            <input type="date" id="date" name="category_data[date]"
                                max="{{ now()->format('Y-m-d') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="${window.oldFormData['category_data']?.date || ''}">
                        </div>
                    `;
                } else if (categoryId == '23') { // Toll
                    fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700">Date From *</label>
                                <input type="date" id="date_from" name="category_data[date_from]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.date_from || ''}">
                            </div>
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700">Date To *</label>
                                <input type="date" id="date_to" name="category_data[date_to]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.date_to || ''}">
                            </div>
                        </div>
                    `;
                } else if (categoryId == '24') { // Phone Bills
                    fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700">Date From *</label>
                                <input type="date" id="date_from" name="category_data[date_from]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.date_from || ''}">
                            </div>
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700">Date To *</label>
                                <input type="date" id="date_to" name="category_data[date_to]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.date_to || ''}">
                            </div>
                        </div>
                    `;
                } else if (categoryId == '25') { // Office Parking
                    fieldsHTML = `
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Date *</label>
                            <input type="date" id="date" name="category_data[date]"
                                max="{{ now()->format('Y-m-d') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="${window.oldFormData['category_data']?.date || ''}">
                        </div>
                    `;
                } else if (categoryId == '26') { // Client Parking
                    fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="partner_customer_name" class="block text-sm font-medium text-gray-700">Partner/Customer's Name *</label>
                                <input type="text" id="partner_customer_name" name="category_data[partner_customer_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter partner or customer name"
                                    value="${window.oldFormData['category_data']?.partner_customer_name || ''}">
                            </div>
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date *</label>
                                <input type="date" id="date" name="category_data[date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.date || ''}">
                            </div>
                        </div>
                    `;
                } else if (categoryId == '27') { // Medical Claim
                    fieldsHTML = `
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Date *</label>
                            <input type="date" id="date" name="category_data[date]"
                                max="{{ now()->format('Y-m-d') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="${window.oldFormData['category_data']?.date || ''}">
                        </div>
                    `;
                } else if (categoryId == '28') { // Other Claims
                    fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name *</label>
                                <input type="text" id="company_name" name="category_data[company_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter company name"
                                    value="${window.oldFormData['category_data']?.company_name || ''}">
                            </div>
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date *</label>
                                <input type="date" id="date" name="category_data[date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.date || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="claim_details" class="block text-sm font-medium text-gray-700">Claim Details *</label>
                            <textarea id="claim_details" name="category_data[claim_details]" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Provide detailed description of the claim...">${window.oldFormData['category_data']?.claim_details || ''}</textarea>
                        </div>
                    `;
                }

                if (fieldsHTML) {
                        categoryFieldsContent.innerHTML = fieldsHTML;
                        categoryFields.classList.remove('hidden');
                    } else {
                        // Show message for unsupported categories
                        categoryFieldsContent.innerHTML = '<p class="text-gray-500 italic">No specific fields required for this category.</p>';
                        categoryFields.classList.remove('hidden');
                    }
                }, 100); // Small delay for UX
            } else {
                categoryFields.classList.add('hidden');
            }
        });

        // Auto-trigger category change on page load if category was previously selected
        if (window.oldFormData.category_id) {
            document.getElementById('category_id').dispatchEvent(new Event('change'));
        }

        // Function to update amount field max based on category
        function updateAmountMax(categoryId) {
            const amountField = document.getElementById('amount');
            const amountHelp = amountField.parentElement.querySelector('.text-xs.text-gray-500');

            // Category max amounts mapping (matches backend validation rules)
            const categoryMaxAmounts = {
                '19': 200.00,   // Entertainment (Meals)
                '20': 500.00,   // Accommodation (per night)
                '21': 2000.00,  // Transportation/Trip
                '22': 300.00,   // Petrol
                '23': 100.00,   // Toll
                '24': 150.00,   // Phone Bills
                '25': 400.00,   // Office Parking (per month)
                '26': 25.00,    // Client Parking (per visit)
                '27': 1000.00,  // Medical Claim
                '28': 500.00    // Other Claims
            };

            const maxAmount = categoryMaxAmounts[categoryId] || 5000.00;
            amountField.max = maxAmount;
            amountHelp.textContent = `Maximum amount: RM ${maxAmount.toFixed(2)}`;
        }

        // Update amount max when category changes
        document.getElementById('category_id').addEventListener('change', function(e) {
            updateAmountMax(e.target.value);
        });

        // Set initial max amount
        updateAmountMax(document.getElementById('category_id').value);

    </script>
</x-app-layout>
</x-app-layout>
</x-app-layout>
</x-app-layout>
</x-app-layout>