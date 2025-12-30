<x-mail::message>
# Login Verification Code

Hello {{ $user->name }},

You have requested to log in to your account. Please use the following verification code to complete your login:

## Your Verification Code

<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #dc2626; padding: 20px; background: #fef2f2; border-radius: 8px; margin: 20px 0;">
{{ $code }}
</div>

**This code will expire in 5 minutes.**

## Security Notice

⚠️ **Never share this code with anyone.**  
If you did not request this code, please ignore this email or contact support immediately.

**Login Details:**
- **Time:** {{ now()->format('d M Y, h:i A') }}
- **IP Address:** {{ $twoFactorCode->ip_address ?? 'N/A' }}

Thanks,<br>
{{ config('app.name') }} Security Team
</x-mail::message>
