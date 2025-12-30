<x-mail::message>
# Claim Approved! ✅

Hello {{ $claim->user->name }},

Great news! Your expense claim has been approved.

## Claim Details

**Title:** {{ $claim->title }}  
**Category:** {{ $category->display_name ?? $category->name }}  
**Claimed Amount:** RM {{ number_format($claim->amount, 2) }}  
**Approved Amount:** RM {{ number_format($claim->approved_amount, 2) }}  
**Claim Date:** {{ $claim->claim_date->format('d M Y') }}  
**Approved At:** {{ $claim->approved_at->format('d M Y, h:i A') }}

@if($claim->approved_amount < $claim->amount)
**Note:** Your claim was partially approved. The approved amount is less than the claimed amount.
@endif

@if($claim->admin_notes)
## Admin Notes
{{ $claim->admin_notes }}
@endif

<x-mail::button :url="$url">
View Claim Details
</x-mail::button>

Thank you for submitting your claim. The approved amount will be processed for payment.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
