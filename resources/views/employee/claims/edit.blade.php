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
                        <a href="{{ route('employee.claims.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
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

                {{-- <pre>
                    {{ json_encode($claim, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                </pre> --}}

                <form action="{{ route('employee.claims.update', ['claim' => $claim->id]) }}" method="POST"
                    enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Claim Title
                                    *</label>
                                <input type="text" id="title" name="title" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="e.g., Business Lunch with Client"
                                    value="{{ old('title', $claim->title) }}">
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Claim Category
                                    *</label>
                                <select id="category_id" name="category_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select a category...</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $claim->category->id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->display_name }} (Max: RM
                                            {{ number_format($category->getMaxAmount(), 2) }})</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Select the appropriate category for your expense
                                </p>
                            </div>

                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Amount (RM)
                                    *</label>
                                <input type="number" id="amount" name="amount" required step="0.01"
                                    min="0.01" max="5000"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="0.00" value="{{ old('amount', $claim->amount) }}">
                                <p class="mt-1 text-xs text-gray-500">Maximum amount: RM 5,000.00</p>
                            </div>

                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700">Currency *</label>
                                <select id="currency" name="currency" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="MYR"
                                        {{ old('currency', $claim->currency) == 'MYR' ? 'selected' : '' }}>MYR -
                                        Malaysian Ringgit</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">All claims must be in Malaysian Ringgit</p>
                            </div>

                            <div>
                                <label for="claim_date" class="block text-sm font-medium text-gray-700">Claim Date
                                    *</label>
                                <input type="date" id="claim_date" name="claim_date" required
                                    max="{{ now()->format('Y-m-d') }}" min="{{ now()->subDays(30)->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('claim_date', optional($claim->claim_date)->format('Y-m-d')) }}">
                                <p class="mt-1 text-xs text-gray-500">Within last 30 days only</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description
                                *</label>
                            <textarea id="description" name="description" rows="3" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Provide detailed description of the expense...">{{ old('description', $claim->description) }}</textarea>
                        </div>

                        <div class="mt-4">
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select id="priority" name="priority"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="low"
                                    {{ old('priority', $claim->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="normal"
                                    {{ old('priority', $claim->priority) == 'normal' ? 'selected' : '' }}>Normal
                                </option>
                                <option value="high"
                                    {{ old('priority', $claim->priority) == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent"
                                    {{ old('priority', $claim->priority) == 'urgent' ? 'selected' : '' }}>Urgent
                                </option>
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
                            <label for="receipts" class="block text-sm font-medium text-gray-700">Upload Receipts
                                *</label>

                            <div
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">

                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <div class="flex text-sm text-gray-600">
                                        <label for="receipts"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload files</span>

                                            <!-- Leave this input EMPTY — required by browser security -->
                                            <input id="receipts" name="receipts[]" type="file" class="sr-only"
                                                multiple accept=".jpg,.jpeg,.png,.pdf">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>

                                    <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB each (max 5 files)</p>
                                </div>

                                <div id="file-list" class="mt-4 space-y-2"></div>
                            </div>
                        </div>

                        <!-- ✅ Existing uploaded files -->
                        @if (!empty($claim->receipts))
                            <h4 class="font-semibold text-gray-800 mt-4 mb-2">Existing Documents</h4>

                            <div id="existing-files" class="space-y-2">
                                @foreach ($claim->receipts as $file)
                                    <div
                                        class="flex items-center justify-between p-3 bg-white border rounded-lg shadow-sm">

                                        <div class="flex items-center space-x-3">

                                            {{-- File Type Icon --}}
                                            @if ($file->file_type === 'pdf')
                                                <svg class="w-6 h-6 text-red-600" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path d="M6 2a2 2 0 00-2 2v12c0 1.1.9 2 2 2h8a2
                                     2 0 002-2V6l-6-4H6z" />
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-blue-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2
                                     2 0 002-2V7l-6-4H4z" />
                                                </svg>
                                            @endif

                                            {{-- File Name (clickable preview) --}}
                                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                                class="text-sm text-indigo-600 hover:underline">
                                                {{ $file->file_name }}
                                            </a>

                                            {{-- Optional: Primary badge --}}
                                            @if ($file->is_primary)
                                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">
                                                    Primary
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Delete Button --}}
                                        <button type="button"
                                            class="text-red-600 text-xs delete-existing-file hover:underline"
                                            data-file="{{ $file->file_path }}">
                                            Remove
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif


                        <!-- New uploaded files preview (your original section) -->
                        <div id="receipt-preview"
                            class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 hidden">
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
        window.oldFormData = @json($claim);

        // Convert category_data JSON string to object if needed
        if (typeof window.oldFormData.category_data === 'string') {
            try {
                window.oldFormData.category_data = JSON.parse(window.oldFormData.category_data);
            } catch (e) {
                window.oldFormData.category_data = {};
            }
        }
        console.log("window.oldFormData.category_data", window.oldFormData.category_data)

        // console.log(window.oldFormData.category_data)

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

        // Handle deleting existing uploaded files
        document.querySelectorAll('.delete-existing-file').forEach(button => {
            button.addEventListener('click', function() {

                // Remove row from UI
                this.closest('div').remove();

                // Add hidden input so Laravel knows we want to delete the file
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_files[]';
                input.value = this.dataset.file;

                document.querySelector('form').appendChild(input);
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
                categoryFieldsContent.innerHTML =
                    '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div><p class="mt-2 text-sm text-gray-600">Loading category fields...</p></div>';

                // Small delay to show loading state, then render fields
                setTimeout(() => {

                    // Add category-specific fields based on selection
                    let fieldsHTML = '';

                    // New 10-category structure (IDs 19-28)
                    if (categoryId == '19') { // Entertainment (Meals)
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700">Location *</label>
                                <input type="text" id="location" name="category_data[location]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter restaurant or venue location"
                                    value="${window.oldFormData['category_data']?.location || ''}">
                            </div>
                            <div>
                                <label for="attendees_count" class="block text-sm font-medium text-gray-700">Number of Attendees *</label>
                                <input type="number" id="attendees_count" name="category_data[attendees_count]" min="1" max="20"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter number of attendees"
                                    value="${window.oldFormData['category_data']?.attendees_count || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="meeting_purpose" class="block text-sm font-medium text-gray-700">Business Purpose *</label>
                            <textarea id="meeting_purpose" name="category_data[meeting_purpose]" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Describe the business purpose of the meal or entertainment...">${window.oldFormData['category_data']?.meeting_purpose || ''}</textarea>
                        </div>
                    `;
                    } else if (categoryId == '20') { // Accommodation
                        fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="hotel_name" class="block text-sm font-medium text-gray-700">Hotel Name *</label>
                                <input type="text" id="hotel_name" name="category_data[hotel_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter hotel or accommodation name"
                                    value="${(window.oldFormData.category_data && window.oldFormData.category_data.hotel_name) ? window.oldFormData.category_data.hotel_name : ''}">
                            </div>
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700">Location *</label>
                                <input type="text" id="location" name="category_data[location]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter city and country"
                                    value="${(window.oldFormData.category_data && window.oldFormData.category_data.location) ? window.oldFormData.category_data.location : ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="check_in_date" class="block text-sm font-medium text-gray-700">Check-in Date *</label>
                                <input type="date" id="check_in_date" name="category_data[check_in_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${(window.oldFormData.category_data && window.oldFormData.category_data.check_in_date) ? window.oldFormData.category_data.check_in_date : ''}">
                            </div>
                            <div>
                                <label for="check_out_date" class="block text-sm font-medium text-gray-700">Check-out Date *</label>
                                <input type="date" id="check_out_date" name="category_data[check_out_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${(window.oldFormData.category_data && window.oldFormData.category_data.check_out_date) ? window.oldFormData.category_data.check_out_date : ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="room_type" class="block text-sm font-medium text-gray-700">Room Type *</label>
                                <select id="room_type" name="category_data[room_type]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select room type...</option>
                                    <option value="standard" ${(window.oldFormData.category_data && window.oldFormData.category_data.room_type == 'standard') ? 'selected' : ''}>Standard</option>
                                    <option value="deluxe" ${(window.oldFormData.category_data && window.oldFormData.category_data.room_type == 'deluxe') ? 'selected' : ''}>Deluxe</option>
                                    <option value="suite" ${(window.oldFormData.category_data && window.oldFormData.category_data.room_type == 'suite') ? 'selected' : ''}>Suite</option>
                                    <option value="other" ${(window.oldFormData.category_data && window.oldFormData.category_data.room_type == 'other') ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="booking_reference" class="block text-sm font-medium text-gray-700">Booking Reference</label>
                                <input type="text" id="booking_reference" name="category_data[booking_reference]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter booking confirmation number"
                                    value="${(window.oldFormData.category_data && window.oldFormData.category_data.booking_reference) ? window.oldFormData.category_data.booking_reference : ''}">
                            </div>
                        </div>
                    `;
                    } else if (categoryId == '21') { // Transportation/Trip
                        fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="travel_from" class="block text-sm font-medium text-gray-700">From *</label>
                                <input type="text" id="travel_from" name="category_data[travel_from]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter departure location"
                                    value="${window.oldFormData['category_data']?.travel_from || ''}">
                            </div>
                            <div>
                                <label for="travel_to" class="block text-sm font-medium text-gray-700">To *</label>
                                <input type="text" id="travel_to" name="category_data[travel_to]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter destination"
                                    value="${window.oldFormData['category_data']?.travel_to || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="travel_purpose" class="block text-sm font-medium text-gray-700">Travel Purpose *</label>
                            <textarea id="travel_purpose" name="category_data[travel_purpose]" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Describe the purpose of travel...">${window.oldFormData['category_data']?.travel_purpose || ''}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="transport_mode" class="block text-sm font-medium text-gray-700">Transport Mode *</label>
                                <select id="transport_mode" name="category_data[transport_mode]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select mode...</option>
                                    <option value="flight" ${window.oldFormData['category_data']?.transport_mode == 'flight' ? 'selected' : ''}>Flight</option>
                                    <option value="train" ${window.oldFormData['category_data']?.transport_mode == 'train' ? 'selected' : ''}>Train</option>
                                    <option value="bus" ${window.oldFormData['category_data']?.transport_mode == 'bus' ? 'selected' : ''}>Bus</option>
                                    <option value="taxi" ${window.oldFormData['category_data']?.transport_mode == 'taxi' ? 'selected' : ''}>Taxi/Rideshare</option>
                                    <option value="rental_car" ${window.oldFormData['category_data']?.transport_mode == 'rental_car' ? 'selected' : ''}>Rental Car</option>
                                    <option value="other" ${window.oldFormData['category_data']?.transport_mode == 'other' ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="departure_date" class="block text-sm font-medium text-gray-700">Departure Date *</label>
                                <input type="date" id="departure_date" name="category_data[departure_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.departure_date || ''}">
                            </div>
                            <div>
                                <label for="return_date" class="block text-sm font-medium text-gray-700">Return Date</label>
                                <input type="date" id="return_date" name="category_data[return_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.return_date || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="booking_reference" class="block text-sm font-medium text-gray-700">Booking Reference</label>
                            <input type="text" id="booking_reference" name="category_data[booking_reference]"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Enter booking confirmation number"
                                value="${window.oldFormData['category_data']?.booking_reference || ''}">
                        </div>
                    `;
                    } else if (categoryId == '22') { // Petrol
                        fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="vehicle_details" class="block text-sm font-medium text-gray-700">Vehicle Details *</label>
                                <input type="text" id="vehicle_details" name="category_data[vehicle_details]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter make, model, and plate number"
                                    value="${window.oldFormData['category_data']?.vehicle_details || ''}">
                            </div>
                            <div>
                                <label for="fuel_type" class="block text-sm font-medium text-gray-700">Fuel Type *</label>
                                <select id="fuel_type" name="category_data[fuel_type]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select fuel type...</option>
                                    <option value="petrol" ${window.oldFormData['category_data']?.fuel_type == 'petrol' ? 'selected' : ''}>Petrol</option>
                                    <option value="diesel" ${window.oldFormData['category_data']?.fuel_type == 'diesel' ? 'selected' : ''}>Diesel</option>
                                    <option value="hybrid" ${window.oldFormData['category_data']?.fuel_type == 'hybrid' ? 'selected' : ''}>Hybrid</option>
                                    <option value="electric" ${window.oldFormData['category_data']?.fuel_type == 'electric' ? 'selected' : ''}>Electric</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="odometer_start" class="block text-sm font-medium text-gray-700">Odometer Start Reading *</label>
                                <input type="number" id="odometer_start" name="category_data[odometer_start]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Starting odometer"
                                    value="${window.oldFormData['category_data']?.odometer_start || ''}">
                            </div>
                            <div>
                                <label for="odometer_end" class="block text-sm font-medium text-gray-700">Odometer End Reading *</label>
                                <input type="number" id="odometer_end" name="category_data[odometer_end]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ending odometer"
                                    value="${window.oldFormData['category_data']?.odometer_end || ''}">
                            </div>
                            <div>
                                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date *</label>
                                <input type="date" id="purchase_date" name="category_data[purchase_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.purchase_date || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="station_name" class="block text-sm font-medium text-gray-700">Station Name *</label>
                            <input type="text" id="station_name" name="category_data[station_name]"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Enter petrol station name"
                                value="${window.oldFormData['category_data']?.station_name || ''}">
                        </div>
                    `;
                    } else if (categoryId == '23') { // Toll
                        fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="route_from" class="block text-sm font-medium text-gray-700">Route From *</label>
                                <input type="text" id="route_from" name="category_data[route_from]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter starting point"
                                    value="${window.oldFormData['category_data']?.route_from || ''}">
                            </div>
                            <div>
                                <label for="route_to" class="block text-sm font-medium text-gray-700">Route To *</label>
                                <input type="text" id="route_to" name="category_data[route_to]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter destination"
                                    value="${window.oldFormData['category_data']?.route_to || ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="toll_plaza_name" class="block text-sm font-medium text-gray-700">Toll Plaza Name *</label>
                                <input type="text" id="toll_plaza_name" name="category_data[toll_plaza_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter toll plaza name"
                                    value="${window.oldFormData['category_data']?.toll_plaza_name || ''}">
                            </div>
                            <div>
                                <label for="travel_date" class="block text-sm font-medium text-gray-700">Travel Date *</label>
                                <input type="date" id="travel_date" name="category_data[travel_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.travel_date || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="vehicle_type" class="block text-sm font-medium text-gray-700">Vehicle Type *</label>
                            <select id="vehicle_type" name="category_data[vehicle_type]"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select vehicle type...</option>
                                <option value="motorcycle" ${window.oldFormData['category_data']?.vehicle_type == 'motorcycle' ? 'selected' : ''}>Motorcycle</option>
                                <option value="car" ${window.oldFormData['category_data']?.vehicle_type == 'car' ? 'selected' : ''}>Car</option>
                                <option value="van" ${window.oldFormData['category_data']?.vehicle_type == 'van' ? 'selected' : ''}>Van</option>
                                <option value="truck" ${window.oldFormData['category_data']?.vehicle_type == 'truck' ? 'selected' : ''}>Truck</option>
                                <option value="other" ${window.oldFormData['category_data']?.vehicle_type == 'other' ? 'selected' : ''}>Other</option>
                            </select>
                        </div>
                    `;
                    } else if (categoryId == '24') { // Phone Bills
                        fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="service_provider" class="block text-sm font-medium text-gray-700">Service Provider *</label>
                                <input type="text" id="service_provider" name="category_data[service_provider]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter telecom provider name"
                                    value="${window.oldFormData['category_data']?.service_provider || ''}">
                            </div>
                            <div>
                                <label for="account_number" class="block text-sm font-medium text-gray-700">Account Number *</label>
                                <input type="text" id="account_number" name="category_data[account_number]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter account or phone number"
                                    value="${window.oldFormData['category_data']?.account_number || ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="billing_period_start" class="block text-sm font-medium text-gray-700">Billing Period Start *</label>
                                <input type="date" id="billing_period_start" name="category_data[billing_period_start]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.billing_period_start || ''}">
                            </div>
                            <div>
                                <label for="billing_period_end" class="block text-sm font-medium text-gray-700">Billing Period End *</label>
                                <input type="date" id="billing_period_end" name="category_data[billing_period_end]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.billing_period_end || ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="business_usage_percentage" class="block text-sm font-medium text-gray-700">Business Usage % *</label>
                                <input type="number" id="business_usage_percentage" name="category_data[business_usage_percentage]" min="0" max="100" step="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter percentage (0-100)"
                                    value="${window.oldFormData['category_data']?.business_usage_percentage || ''}">
                            </div>
                            <div>
                                <label for="plan_type" class="block text-sm font-medium text-gray-700">Plan Type *</label>
                                <select id="plan_type" name="category_data[plan_type]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select plan type...</option>
                                    <option value="prepaid" ${window.oldFormData['category_data']?.plan_type == 'prepaid' ? 'selected' : ''}>Prepaid</option>
                                    <option value="postpaid" ${window.oldFormData['category_data']?.plan_type == 'postpaid' ? 'selected' : ''}>Postpaid</option>
                                    <option value="corporate" ${window.oldFormData['category_data']?.plan_type == 'corporate' ? 'selected' : ''}>Corporate</option>
                                    <option value="other" ${window.oldFormData['category_data']?.plan_type == 'other' ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                        </div>
                    `;
                    } else if (categoryId == '25') { // Office Parking
                        fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="parking_location" class="block text-sm font-medium text-gray-700">Parking Location *</label>
                                <input type="text" id="parking_location" name="category_data[parking_location]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter parking location details"
                                    value="${window.oldFormData['category_data']?.parking_location || ''}">
                            </div>
                            <div>
                                <label for="parking_duration" class="block text-sm font-medium text-gray-700">Parking Duration *</label>
                                <select id="parking_duration" name="category_data[parking_duration]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select duration...</option>
                                    <option value="daily" ${window.oldFormData['category_data']?.parking_duration == 'daily' ? 'selected' : ''}>Daily</option>
                                    <option value="weekly" ${window.oldFormData['category_data']?.parking_duration == 'weekly' ? 'selected' : ''}>Weekly</option>
                                    <option value="monthly" ${window.oldFormData['category_data']?.parking_duration == 'monthly' ? 'selected' : ''}>Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                                <input type="date" id="start_date" name="category_data[start_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.start_date || ''}">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" id="end_date" name="category_data[end_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.end_date || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="parking_type" class="block text-sm font-medium text-gray-700">Parking Type</label>
                            <select id="parking_type" name="category_data[parking_type]"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select parking type...</option>
                                <option value="covered" ${window.oldFormData['category_data']?.parking_type == 'covered' ? 'selected' : ''}>Covered</option>
                                <option value="open" ${window.oldFormData['category_data']?.parking_type == 'open' ? 'selected' : ''}>Open</option>
                                <option value="seasonal" ${window.oldFormData['category_data']?.parking_type == 'seasonal' ? 'selected' : ''}>Seasonal</option>
                            </select>
                        </div>
                    `;
                    } else if (categoryId == '26') { // Client Parking
                        fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="client_name" class="block text-sm font-medium text-gray-700">Client Name *</label>
                                <input type="text" id="client_name" name="category_data[client_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter client contact person"
                                    value="${window.oldFormData.category_data.client_name || ''}">
                            </div>
                            <div>
                                <label for="client_company" class="block text-sm font-medium text-gray-700">Client Company *</label>
                                <input type="text" id="client_company" name="category_data[client_company]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter client company name"
                                    value="${window.oldFormData.category_data.client_company || ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="parking_location" class="block text-sm font-medium text-gray-700">Parking Location *</label>
                                <input type="text" id="parking_location" name="category_data[parking_location]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter parking location or address"
                                    value="${window.oldFormData.category_data.parking_location || ''}">
                            </div>
                            <div>
                                <label for="visit_date" class="block text-sm font-medium text-gray-700">Visit Date *</label>
                                <input type="date" id="visit_date" name="category_data[visit_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData.category_data.visit_date || ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="duration_hours" class="block text-sm font-medium text-gray-700">Duration (Hours) *</label>
                                <input type="number" id="duration_hours" name="category_data[duration_hours]" min="0.5" max="24" step="0.5"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter duration in hours"
                                    value="${window.oldFormData.category_data.duration_hours || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="meeting_purpose" class="block text-sm font-medium text-gray-700">Meeting Purpose *</label>
                            <textarea id="meeting_purpose" name="category_data[meeting_purpose]" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Describe the purpose of the client meeting...">${window.oldFormData.category_data.meeting_purpose || ''}</textarea>
                        </div>
                    `;
                    } else if (categoryId == '27') { // Medical Claim
                        fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="medical_provider" class="block text-sm font-medium text-gray-700">Medical Provider/Hospital *</label>
                                <input type="text" id="medical_provider" name="category_data[medical_provider]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter hospital or clinic name"
                                    value="${window.oldFormData['category_data']?.medical_provider || ''}">
                            </div>
                            <div>
                                <label for="patient_name" class="block text-sm font-medium text-gray-700">Patient Name *</label>
                                <input type="text" id="patient_name" name="category_data[patient_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter patient name"
                                    value="${window.oldFormData['category_data']?.patient_name || ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="treatment_type" class="block text-sm font-medium text-gray-700">Treatment Type *</label>
                                <select id="treatment_type" name="category_data[treatment_type]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select treatment type...</option>
                                    <option value="consultation" ${window.oldFormData['category_data']?.treatment_type == 'consultation' ? 'selected' : ''}>Consultation</option>
                                    <option value="treatment" ${window.oldFormData['category_data']?.treatment_type == 'treatment' ? 'selected' : ''}>Treatment</option>
                                    <option value="surgery" ${window.oldFormData['category_data']?.treatment_type == 'surgery' ? 'selected' : ''}>Surgery</option>
                                    <option value="medication" ${window.oldFormData['category_data']?.treatment_type == 'medication' ? 'selected' : ''}>Medication</option>
                                    <option value="therapy" ${window.oldFormData['category_data']?.treatment_type == 'therapy' ? 'selected' : ''}>Therapy</option>
                                    <option value="other" ${window.oldFormData['category_data']?.treatment_type == 'other' ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="treatment_date" class="block text-sm font-medium text-gray-700">Treatment Date *</label>
                                <input type="date" id="treatment_date" name="category_data[treatment_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.treatment_date || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="medical_condition" class="block text-sm font-medium text-gray-700">Medical Condition *</label>
                            <textarea id="medical_condition" name="category_data[medical_condition]" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Describe the medical condition or symptoms...">${window.oldFormData['category_data']?.medical_condition || ''}</textarea>
                        </div>
                        <div class="mt-4">
                            <label for="prescription_required" class="block text-sm font-medium text-gray-700">Prescription Required?</label>
                            <select id="prescription_required_select"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select...</option>
                                <option value="1" ${window.oldFormData['category_data']?.prescription_required === true || window.oldFormData['category_data']?.prescription_required === '1' ? 'selected' : ''}>Yes</option>
                                <option value="0" ${window.oldFormData['category_data']?.prescription_required === false || window.oldFormData['category_data']?.prescription_required === '0' ? 'selected' : ''}>No</option>
                            </select>
                            <input type="hidden" id="prescription_required" name="category_data[prescription_required]" value="">
                        </div>
                    `;
                    } else if (categoryId == '28') { // Other Claims
                        fieldsHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="expense_type" class="block text-sm font-medium text-gray-700">Expense Type *</label>
                                <select id="expense_type" name="category_data[expense_type]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select expense type...</option>
                                    <option value="supplies" ${window.oldFormData['category_data']?.expense_type == 'supplies' ? 'selected' : ''}>Office Supplies</option>
                                    <option value="software" ${window.oldFormData['category_data']?.expense_type == 'software' ? 'selected' : ''}>Software/License</option>
                                    <option value="maintenance" ${window.oldFormData['category_data']?.expense_type == 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                    <option value="utilities" ${window.oldFormData['category_data']?.expense_type == 'utilities' ? 'selected' : ''}>Utilities</option>
                                    <option value="professional_fees" ${window.oldFormData['category_data']?.expense_type == 'professional_fees' ? 'selected' : ''}>Professional Fees</option>
                                    <option value="insurance" ${window.oldFormData['category_data']?.expense_type == 'insurance' ? 'selected' : ''}>Insurance</option>
                                    <option value="other" ${window.oldFormData['category_data']?.expense_type == 'other' ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="supplier_name" class="block text-sm font-medium text-gray-700">Supplier Name *</label>
                                <input type="text" id="supplier_name" name="category_data[supplier_name]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter supplier or vendor name"
                                    value="${window.oldFormData['category_data']?.supplier_name || ''}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date *</label>
                                <input type="date" id="purchase_date" name="category_data[purchase_date]"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="${window.oldFormData['category_data']?.purchase_date || ''}">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="expense_details" class="block text-sm font-medium text-gray-700">Expense Details *</label>
                            <textarea id="expense_details" name="category_data[expense_details]" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Provide detailed description of the expense...">${window.oldFormData['category_data']?.expense_details || ''}</textarea>
                        </div>
                        <div class="mt-4">
                            <label for="justification" class="block text-sm font-medium text-gray-700">Business Justification *</label>
                            <textarea id="justification" name="category_data[justification]" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Explain why this expense is necessary for business purposes...">${window.oldFormData['category_data']?.justification || ''}</textarea>
                        </div>
                    `;
                    }

                    if (fieldsHTML) {
                        categoryFieldsContent.innerHTML = fieldsHTML;
                        categoryFields.classList.remove('hidden');
                    } else {
                        // Show message for unsupported categories
                        categoryFieldsContent.innerHTML =
                            '<p class="text-gray-500 italic">No specific fields required for this category.</p>';
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
                '19': 200.00, // Entertainment (Meals)
                '20': 500.00, // Accommodation (per night)
                '21': 2000.00, // Transportation/Trip
                '22': 300.00, // Petrol
                '23': 100.00, // Toll
                '24': 150.00, // Phone Bills
                '25': 400.00, // Office Parking (per month)
                '26': 25.00, // Client Parking (per visit)
                '27': 1000.00, // Medical Claim
                '28': 500.00 // Other Claims
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

        // Handle form submission to convert boolean string values to actual booleans
        document.querySelector('form').addEventListener('submit', function(e) {
            const prescriptionSelect = document.getElementById('prescription_required_select');
            const prescriptionHidden = document.getElementById('prescription_required');
            if (prescriptionSelect && prescriptionHidden && prescriptionSelect.value) {
                // Submit "1" or "0" which Laravel's boolean validation will accept
                prescriptionHidden.value = prescriptionSelect.value;
                console.log('Prescription required value set to:', prescriptionHidden.value);
            }
        });

        // Add change listener for the prescription select to update hidden field immediately
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'prescription_required_select') {
                const prescriptionHidden = document.getElementById('prescription_required');
                if (prescriptionHidden && e.target.value) {
                    prescriptionHidden.value = e.target.value;
                } else if (prescriptionHidden) {
                    prescriptionHidden.value = '';
                }
            }
        });
    </script>
</x-app-layout>
