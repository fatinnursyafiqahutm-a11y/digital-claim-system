<?php

namespace App\Services;

use App\Models\TwoFactorCode;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TwoFactorService
{
    /**
     * Generate and send 2FA code to user.
     */
    public function generateAndSendCode(User $user, string $ipAddress = null, string $userAgent = null): TwoFactorCode
    {
        try {
            DB::beginTransaction();

            // Invalidate any existing unused codes for this user
            TwoFactorCode::where('user_id', $user->id)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->update(['used' => true]);

            // Generate 6-digit code
            $code = $this->generateCode();

            // Create 2FA code record (expires in 5 minutes)
            $twoFactorCode = TwoFactorCode::create([
                'user_id' => $user->id,
                'code' => $code,
                'email' => $user->email,
                'expires_at' => now()->addMinutes(5),
                'used' => false,
                'ip_address' => $ipAddress ?? request()->ip(),
                'user_agent' => $userAgent ?? request()->userAgent(),
            ]);

            // Send code via email
            $user->notify(new TwoFactorCodeNotification($twoFactorCode));

            Log::info('2FA code generated and sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'code_id' => $twoFactorCode->id,
            ]);

            DB::commit();

            return $twoFactorCode;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate 2FA code: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            throw $e;
        }
    }

    /**
     * Verify 2FA code.
     */
    public function verifyCode(int $userId, string $code): bool
    {
        $twoFactorCode = TwoFactorCode::forUser($userId)
            ->byCode($code)
            ->valid()
            ->latest()
            ->first();

        if (!$twoFactorCode) {
            Log::warning('Invalid 2FA code attempted', [
                'user_id' => $userId,
                'code' => $code,
            ]);
            return false;
        }

        // Mark code as used
        $twoFactorCode->markAsUsed();

        Log::info('2FA code verified successfully', [
            'user_id' => $userId,
            'code_id' => $twoFactorCode->id,
        ]);

        return true;
    }

    /**
     * Generate a 6-digit code.
     */
    private function generateCode(): string
    {
        // Generate random 6-digit code (000000 to 999999)
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Clean up expired codes (can be called by a scheduled task).
     */
    public function cleanupExpiredCodes(): int
    {
        $deleted = TwoFactorCode::where('expires_at', '<', now())
            ->orWhere(function ($query) {
                $query->where('used', true)
                    ->where('created_at', '<', now()->subDays(1));
            })
            ->delete();

        Log::info('Cleaned up expired 2FA codes', [
            'deleted_count' => $deleted,
        ]);

        return $deleted;
    }
}

