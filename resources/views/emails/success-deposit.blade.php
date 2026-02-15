@extends('emails.layout')

@section('content')
@if ($foramin)
<h1>New Deposit Notification</h1>

<p>Hello Admin,</p>
<p>A new deposit has been received and requires your attention.</p>

<h2>Deposit Details</h2>

<table class="details-table">
    <tr>
        <td>User</td>
        <td>{{ $user->name }}</td>
    </tr>
    <tr>
        <td>Email</td>
        <td>{{ $user->email }}</td>
    </tr>
    <tr>
        <td>Amount</td>
        <td>{{ $user->currency }}{{ number_format($deposit->amount, 2) }}</td>
    </tr>
    <tr>
        <td>Payment Method</td>
        <td>{{ $deposit->payment_mode ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>{{ $deposit->status }}</td>
    </tr>
    <tr>
        <td>Date</td>
        <td>{{ $deposit->created_at->format('F j, Y g:i A') }}</td>
    </tr>
</table>

@if ($deposit->status != "Processed")
<div class="text-center">
    <a href="{{ $settings->site_address }}/admin/deposits" class="button">Process Deposit</a>
</div>
@endif

<p>Please review and process this deposit at your earliest convenience.</p>

<p style="margin-top: 30px;">Best regards,<br><strong>{{ $settings->site_name }} System</strong></p>
@else
@if ($deposit->status == 'Processed')
<h1>✓ Deposit Confirmed</h1>

<p>Hello {{ $user->name }},</p>
<p>Great news! Your deposit has been successfully processed and your account has been credited.</p>

<div class="success-box">
    <p><strong>Your funds are now available!</strong></p>
</div>

<h2>Transaction Summary</h2>

<table class="details-table">
    <tr>
        <td>Amount Deposited</td>
        <td>{{ $user->currency }}{{ number_format($deposit->amount, 2) }}</td>
    </tr>
    <tr>
        <td>Payment Method</td>
        <td>{{ $deposit->payment_mode ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>Transaction ID</td>
        <td>#{{ $deposit->id }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>Confirmed ✓</td>
    </tr>
    <tr>
        <td>Date</td>
        <td>{{ $deposit->created_at->format('F j, Y g:i A') }}</td>
    </tr>
</table>

<p>Your funds are now available and ready to be allocated to trading bots.</p>

<div class="text-center">
    <a href="{{ $settings->site_address }}/user/dashboard" class="button">Start Trading</a>
</div>

<h2>Next Steps</h2>

<ul class="features-list">
    <li>Browse our available trading bots</li>
    <li>Select a strategy that matches your goals</li>
    <li>Allocate funds and activate your bot</li>
    <li>Monitor your portfolio performance</li>
</ul>

<p>Thank you for trusting {{ $settings->site_name }} with your investment.</p>

<p style="margin-top: 30px;">Best regards,<br><strong>The {{ $settings->site_name }} Team</strong></p>
@else
<h1>Deposit Received</h1>

<p>Hello {{ $user->name }},</p>
<p>Thank you for your deposit! We have successfully received your transaction and it is currently being processed.</p>

<div class="info-box">
    <p><strong>Processing Status: ⏳</strong> Your deposit is being verified by our team.</p>
</div>

<h2>Transaction Details</h2>

<table class="details-table">
    <tr>
        <td>Amount</td>
        <td>{{ $user->currency }}{{ number_format($deposit->amount, 2) }}</td>
    </tr>
    <tr>
        <td>Payment Method</td>
        <td>{{ $deposit->payment_mode ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>Transaction ID</td>
        <td>#{{ $deposit->id }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>Processing</td>
    </tr>
    <tr>
        <td>Submitted</td>
        <td>{{ $deposit->created_at->format('F j, Y g:i A') }}</td>
    </tr>
</table>

<h2>What Happens Next?</h2>

<p>Our team is currently verifying your deposit. This process typically takes:</p>

<ul class="features-list">
    <li><strong>Crypto:</strong> 15-30 minutes (network confirmations)</li>
    <li><strong>Bank Transfer:</strong> 1-3 business days</li>
    <li><strong>Other Methods:</strong> As per payment provider timeline</li>
</ul>

<p>You will receive a confirmation email once your deposit has been verified and your account credited.</p>

<div class="text-center">
    <a href="{{ $settings->site_address }}/dashboard/deposits" class="button">Track Deposit Status</a>
</div>

<p>If you have any questions or concerns, our support team is here to help.</p>

<p style="margin-top: 30px;">Best regards,<br><strong>The {{ $settings->site_name }} Team</strong></p>
@endif
@endif

<div class="divider"></div>

<p class="text-muted" style="font-size: 13px;">For security reasons, please verify this transaction in your account dashboard. If you did not initiate this deposit, contact support immediately.</p>
@endsection
