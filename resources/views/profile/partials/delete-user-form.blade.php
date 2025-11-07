<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button onclick="openDeleteAccountModal()">
        {{ __('Delete Account') }}
    </x-danger-button>

    <!-- Simple Delete Account Modal -->
    <div id="deleteAccountModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <form method="post" action="{{ route('profile.destroy') }}" class="mt-3">
                @csrf
                @method('delete')

                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ __('Are you sure you want to delete your account?') }}
                </h3>

                <p class="text-sm text-gray-600 mb-4">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Password') }}
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        placeholder="{{ __('Password') }}"
                        required
                    />
                    @if ($errors->userDeletion->has('password'))
                        <p class="mt-1 text-sm text-red-600">{{ $errors->userDeletion->first('password') }}</p>
                    @endif
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteAccountModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        {{ __('Delete Account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Simple Delete Account Modal
        (function() {
            'use strict';

            window.openDeleteAccountModal = function() {
                try {
                    document.getElementById('deleteAccountModal').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } catch (error) {
                    console.error('Error opening delete account modal:', error);
                }
            };

            window.closeDeleteAccountModal = function() {
                try {
                    document.getElementById('deleteAccountModal').classList.add('hidden');
                    document.body.style.overflow = '';
                } catch (error) {
                    console.error('Error closing delete account modal:', error);
                }
            };

            // Close modal when clicking outside
            window.handleDeleteModalClick = function(event) {
                const modal = document.getElementById('deleteAccountModal');
                if (event.target === modal) {
                    closeDeleteAccountModal();
                }
            };

            // Close modal with Escape key
            window.handleDeleteModalEscape = function(event) {
                if (event.key === 'Escape') {
                    closeDeleteAccountModal();
                }
            };

            // Initialize and set up event listeners
            document.addEventListener('click', window.handleDeleteModalClick);
            document.addEventListener('keydown', window.handleDeleteModalEscape);
        })();

        // Auto-open modal if there are validation errors (but only after a brief delay to ensure it's intentional)
        @if ($errors->userDeletion->isNotEmpty())
            setTimeout(function() {
                if (typeof openDeleteAccountModal === 'function') {
                    openDeleteAccountModal();
                }
            }, 100);
        @endif
    </script>
</section>
