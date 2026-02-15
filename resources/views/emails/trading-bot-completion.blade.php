{{-- blade-formatter-disable --}}
@component('mail::message')
# Hello {{ $user->name }},

@if($amount >= 0)
🎉 **Congratulations!** Your trading bot session has completed successfully.
@else
📊 **Trading Session Complete** - Your trading bot session has finished.
@endif

## Trading Session Summary

**Bot Name:** {{ $plan }}
@if($amount >= 0)
**Net Profit:** {{ $user->currency ?? '$' }}{{ number_format($amount, 2) }}
@else
**Net Result:** -{{ $user->currency ?? '$' }}{{ number_format(abs($amount), 2) }}
@endif
**Completion Date:** {{ $plandate }}

@if($amount >= 0)
Your profits have been automatically credited to your account balance and are available for withdrawal or reinvestment.
@else
Your trading session has completed with a net loss. The remaining balance has been returned to your account. Please consider adjusting your trading strategy or contact support for assistance.
@endif

@extends('emails.layout')

@section('content')
<h1>Hello {{ $user->name }},</h1>

@if($amount >= 0)
<div class="success-box">
    <p>🎉 <strong>Congratulations!</strong> Your trading bot session has completed successfully.</p>
</div>
@else
<div class="info-box">
    <p>📊 <strong>Trading Session Complete</strong> - Your trading bot session has finished.</p>
</div>
@endif

<h2>Trading Session Summary</h2>

<table class="details-table">
    <tr>
        <td>Bot Name</td>
        <td>{{ $plan }}</td>
    </tr>
    <tr>
        <td>@if($amount >= 0)Net Profit@else Net Result@endif</td>
        <td style="color: {{ $amount >= 0 ? '#49b336' : '#ff5630' }}; font-weight: 700;">@if($amount >= 0){{ $user->currency ?? '$' }}{{ number_format($amount, 2) }}@else-{{ $user->currency ?? '$' }}{{ number_format(abs($amount), 2) }}@endif</td>
    </tr>
    <tr>
        <td>Completion Date</td>
        <td>{{ $plandate }}</td>
    </tr>
</table>

@if($amount >= 0)
<p>Your profits have been automatically credited to your account balance and are available for withdrawal or reinvestment.</p>
@else
<div class="warning-box">
    <p>Your trading session has completed with a net loss. The remaining balance has been returned to your account. Please consider adjusting your trading strategy or contact support for assistance.</p>
</div>
@endif

<div class="text-center">
    <a href="{{ $settings->site_address }}/user/dashboard" class="button">View Dashboard</a>
</div>

<p>Thank you for using our automated trading services!</p>

<p style="margin-top: 30px;">Best regards,<br><strong>The {{ $settings->site_name ?? 'Trading Platform' }} Team</strong></p>

<div class="divider"></div>

<p class="text-muted" style="font-size: 13px;">This is an automated notification from your trading bot system.</p>
@endsection

Thank you for using our automated trading services!

Best regards,
{{ $settings->site_name ?? 'Trading Platform' }} Team

---
*This is an automated notification from your trading bot system.*
@endcomponent
{{-- blade-formatter-disable --}}
