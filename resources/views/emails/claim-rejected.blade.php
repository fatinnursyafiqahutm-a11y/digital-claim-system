<x-mail::message>
# Claim Rejected

Hello {{ $claim->user->name }},

Unfortunately, your expense claim has been rejected and requires your attention.

## Claim Details

**Title:** {{ $claim->title }}  
**Category:** {{ $category->display_name ?? $category->name }}  
**Amount:** RM {{ number_format($claim->amount, 2) }}  
**Claim Date:** {{ $claim->claim_date->format('d M Y') }}  
**Rejected At:** {{ $claim->rejected_at->format('d M Y, h:i A') }}

## Rejection Reason

{{ $claim->rejection_reason }}

<x-mail::button :url="$url">
View Claim Details
</x-mail::button>

You can edit and resubmit this claim if needed. Please review the rejection reason and make the necessary corrections.

If you have any questions, please contact the finance department.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
