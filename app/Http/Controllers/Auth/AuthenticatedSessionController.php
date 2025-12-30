<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validate credentials first
        $request->authenticate();

        // Get the authenticated user
        $user = Auth::user();

        // Generate and send 2FA code
        try {
            $this->twoFactorService->generateAndSendCode(
                $user,
                $request->ip(),
                $request->userAgent()
            );

            // Store user ID in session for 2FA verification
            // Don't log them in yet - wait for 2FA verification
            $request->session()->put('login.id', $user->id);
            $request->session()->put('login.remember', $request->boolean('remember'));

            // Logout the user (they're not fully authenticated yet)
            Auth::logout();

            Log::info('2FA code sent for login', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return redirect()->route('two-factor.verify')
                ->with('status', 'A verification code has been sent to your email address. Please enter it to complete your login.');

        } catch (\Exception $e) {
            Auth::logout();
            Log::error('Failed to send 2FA code: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
            ]);

            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Failed to send verification code. Please try again.']);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
