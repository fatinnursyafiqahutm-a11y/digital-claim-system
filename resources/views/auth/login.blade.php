<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-6 fade-in">
            <span class="text-lg">ℹ</span>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div class="text-center mb-8">
            <h2 class="heading-2 text-2xl mb-2">Login to your account</h2>
            <p class="text-gray-600">Enter your credentials to access the system</p>
        </div>

        <!-- Email Address -->
        <div>
            <label class="form-label" for="email">{{ __('Email Address') }}</label>
            <input id="email"
                   class="form-input"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   placeholder="you@example.com"
                   required
                   autofocus
                   autocomplete="username">
            @error('email')
                <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label class="form-label" for="password">{{ __('Password') }}</label>
            <input id="password"
                   class="form-input"
                   type="password"
                   name="password"
                   placeholder="Enter your password"
                   required
                   autocomplete="current-password">
            @error('password')
                <div class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me"
                       type="checkbox"
                       class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500 focus:ring-2"
                       name="remember">
                <span class="ms-3 text-sm text-gray-700 font-medium">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-red-600 hover:text-red-700 transition-colors"
                   href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="pt-4">
            <button type="submit" class="btn btn-primary w-full text-lg py-4">
                {{ __('Sign in') }}
            </button>
        </div>

        <!-- Demo Credentials Info -->
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm font-semibold text-blue-900 mb-2">Demo Credentials:</p>
            <div class="text-xs text-blue-800 space-y-1">
                <p><strong>Admin:</strong> admin@digitalclaim.com / password123</p>
                <p><strong>Employee:</strong> employee@digitalclaim.com / password123</p>
            </div>
        </div>
    </form>
</x-guest-layout>
