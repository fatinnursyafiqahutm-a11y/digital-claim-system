<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TwoFactorVerificationController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Display the 2FA verification form.
     */
    public function show(Request $request): View|RedirectResponse
    {
        // Check if user ID is in session (from login step)
        if (!$request->session()->has('login.id')) {
            return redirect()->route('login')
                ->with('error', 'Please login first to receive a verification code.');
        }

        return view('auth.two-factor-verify');
    }

    /**
     * Verify the 2FA code and complete login.
     */
    public function verify(Request $request): RedirectResponse
    {
        // Clean the code (remove spaces, dashes, etc.)
        $rawCode = $request->input('code', '');
        $code = preg_replace('/[^0-9]/', '', $rawCode);

        // Validate the cleaned code
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => 'Please enter the verification code.',
        ]);

        // Additional validation for cleaned code
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['code' => 'The verification code must be exactly 6 digits.']);
        }

        // Get user ID from session
        $userId = $request->session()->get('login.id');

        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please login again.');
        }

        $user = User::find($userId);

        if (!$user) {
            $request->session()->forget('login.id');
            return redirect()->route('login')
                ->with('error', 'User not found. Please login again.');
        }

        // Verify the code (use cleaned code)
        if (!$this->twoFactorService->verifyCode($userId, $code)) {
            Log::warning('2FA verification failed', [
                'user_id' => $userId,
                'code_attempted' => $code,
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['code' => 'Invalid or expired verification code. Please try again or request a new code.']);
        }

        // Code is valid - complete login
        $remember = $request->session()->get('login.remember', false);

        Auth::login($user, $remember);

        // Clear 2FA session data
        $request->session()->forget(['login.id', 'login.remember']);

        // Update last login
        $user->updateLastLogin();

        Log::info('2FA verification successful, user logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Login successful!');
    }

    /**
     * Resend the 2FA code.
     */
    public function resend(Request $request): RedirectResponse
    {
        // Get user ID from session
        $userId = $request->session()->get('login.id');

        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please login again.');
        }

        $user = User::find($userId);

        if (!$user) {
            $request->session()->forget('login.id');
            return redirect()->route('login')
                ->with('error', 'User not found. Please login again.');
        }

        try {
            // Generate and send new code
            $this->twoFactorService->generateAndSendCode(
                $user,
                $request->ip(),
                $request->userAgent()
            );

            Log::info('2FA code resent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return redirect()->back()
                ->with('status', 'A new verification code has been sent to your email address.');

        } catch (\Exception $e) {
            Log::error('Failed to resend 2FA code: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return redirect()->back()
                ->withErrors(['code' => 'Failed to resend verification code. Please try again.']);
        }
    }
}
