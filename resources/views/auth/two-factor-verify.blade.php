<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="heading-2 text-2xl mb-2">Two-Factor Authentication</h2>
        <p class="text-gray-600">Enter the verification code sent to your email</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">{{ session('status') }}</p>
        </div>
    @endif

    <!-- Error Messages -->
    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-800 font-semibold">{{ session('error') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify.post') }}" class="space-y-6">
        @csrf

        <!-- Code Input -->
        <div>
            <label class="form-label" for="code">Verification Code</label>
            <input id="code"
                   class="form-input text-center text-2xl tracking-widest font-mono"
                   type="text"
                   name="code"
                   value="{{ old('code') }}"
                   maxlength="6"
                   pattern="[0-9]{6}"
                   placeholder="000000"
                   required
                   autofocus
                   autocomplete="one-time-code"
                   inputmode="numeric"
                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <p class="mt-2 text-sm text-gray-500">Enter the 6-digit code sent to your email</p>
            @error('code')
                <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="btn btn-primary w-full text-lg py-4">
                Verify & Login
            </button>
        </div>
    </form>

    <!-- Resend Code (separate form) -->
    <div class="text-center mt-4">
        <form method="POST" action="{{ route('two-factor.resend') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-700 transition-colors">
                Resend Code
            </button>
        </form>
    </div>

    <!-- Back to Login -->
    <div class="text-center my-6">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">
            ← Back to Login
        </a>
    </div>
</x-guest-layout>

