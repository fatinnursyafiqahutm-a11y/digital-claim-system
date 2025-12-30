<x-mail::message>
# Welcome to {{ config('app.name') }}!

Hello {{ $user->name }},

Your account has been successfully created. You can now access the Digital Claim System to submit and track your expense claims.

## Your Account Details

**Employee ID:** {{ $user->employee_id }}  
**Email:** {{ $user->email }}  
**Department:** {{ $user->department }}  
**Default Password:** `{{ $password }}`

## Important Security Notice

⚠️ **You must change your password on first login.**

Your default password is your NRIC number. For security reasons, please change it immediately after logging in.

<x-mail::button :url="$loginUrl">
Login to Your Account
</x-mail::button>

## Getting Started

1. Click the login button above or visit: {{ $loginUrl }}
2. Enter your email: **{{ $user->email }}**
3. Enter your default password: **{{ $password }}**
4. You will be prompted to change your password
5. Start submitting your expense claims!

If you have any questions or need assistance, please contact the finance department.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
