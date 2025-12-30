<x-mail::message>
# New Claim Submitted for Review

Hello Finance Admin,

A new expense claim has been submitted and requires your review.

## Claim Details

**Title:** {{ $claim->title }}  
**Employee:** {{ $employee->name }} ({{ $employee->employee_id }})  
**Category:** {{ $category->display_name ?? $category->name }}  
**Amount:** RM {{ number_format($claim->amount, 2) }}  
**Claim Date:** {{ $claim->claim_date->format('d M Y') }}  
**Submitted At:** {{ $claim->submitted_at->format('d M Y, h:i A') }}

## Description
{{ $claim->description }}

<x-mail::button :url="$url">
Review Claim
</x-mail::button>

Please review this claim at your earliest convenience.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
