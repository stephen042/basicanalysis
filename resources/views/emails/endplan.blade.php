@extends('emails.layout')

@section('content')
<h1>Investment Plan Completed</h1>

<p>Hello {{ $demo->receiver_name }},</p>
<p>Congratulations! Your trading bot investment plan has successfully completed its cycle. Your capital has been returned to your account and is now available for withdrawal or reinvestment.</p>

<div class="success-box">
    <p><strong>Plan Completed!</strong> Your capital has been returned to your account.</p>
</div>

<h2>Plan Summary</h2>

<table class="details-table">
    <tr>
        <td>Trading Bot</td>
        <td>{{ $demo->receiver_plan }}</td>
    </tr>
    <tr>
        <td>Capital Returned</td>
        <td>{{ $demo->received_amount }}</td>
    </tr>
    <tr>
        <td>Completion Date</td>
        <td>{{ $demo->date }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>Completed ✓</td>
    </tr>
</table>

<p>Your account balance has been updated with the returned capital.</p>

<div class="text-center">
    <a href="{{ $settings->site_address }}/dashboard" class="button">View Account Balance</a>
</div>

<h2>What's Next?</h2>

<p>You have several options to continue growing your portfolio:</p>

<div class="info-box">
    <p><strong>💰 Withdraw Your Funds</strong><br>
    Transfer your capital securely to your bank account or wallet.</p>
</div>

<div class="info-box">
    <p><strong>🔄 Reinvest for Growth</strong><br>
    Compound your earnings by starting a new trading bot with your returned capital.</p>
</div>

<div class="info-box">
    <p><strong>📈 Upgrade Your Strategy</strong><br>
    Explore our advanced trading bots for potentially higher returns.</p>
</div>

<div class="text-center">
    <a href="{{ $settings->site_address }}/user/trading-bots" class="button button-secondary">Explore Trading Bots</a>
</div>

<p>Thank you for choosing {{ $demo->sender }} as your trusted investment partner. We look forward to helping you achieve your financial goals.</p>

<p style="margin-top: 30px;">Best regards,<br><strong>The {{ $demo->sender }} Team</strong></p>

<div class="divider"></div>

<p class="text-muted" style="font-size: 13px;">Your investment plan has completed successfully. Review your full transaction history and performance analytics in your dashboard.</p>
@endsection
