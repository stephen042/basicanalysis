@extends('emails.layout')

@section('content')
@if ($foramin)
<h1>New Withdrawal Request</h1>

<p>Hello Admin,</p>
<p>A withdrawal request has been submitted and requires your attention.</p>

<h2>Withdrawal Details</h2>

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
        <td>{{ $user->currency }}{{ number_format($withdrawal->amount, 2) }}</td>
    </tr>
    <tr>
        <td>Payment Method</td>
        <td>{{ $withdrawal->payment_mode ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>{{ $withdrawal->status }}</td>
    </tr>
    <tr>
        <td>Requested</td>
        <td>{{ $withdrawal->created_at->format('F j, Y g:i A') }}</td>
    </tr>
</table>

<div class="text-center">
    <a href="{{ $settings->site_address }}/admin/withdrawals" class="button">Process Withdrawal</a>
</div>

<p>Please review and process this withdrawal request at your earliest convenience.</p>

<p style="margin-top: 30px;">Best regards,<br><strong>{{ $settings->site_name }} System</strong></p>
@else
@if ($withdrawal->status == 'Processed')
<h1>✓ Withdrawal Completed</h1>

<p>Hello {{ $user->name }},</p>
<p>Great news! Your withdrawal request has been successfully processed and the funds have been sent to your designated account.</p>

<div class="success-box">
    <p><strong>Withdrawal Successful!</strong> Your funds are on their way.</p>
</div>

<h2>Transaction Summary</h2>

<table class="details-table">
    <tr>
        <td>Amount Withdrawn</td>
        <td>{{ $user->currency }}{{ number_format($withdrawal->amount, 2) }}</td>
    </tr>
    <tr>
        <td>Payment Method</td>
        <td>{{ $withdrawal->payment_mode ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>Transaction ID</td>
        <td>#{{ $withdrawal->id }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>Completed ✓</td>
    </tr>
    <tr>
        <td>Processed</td>
        <td>{{ $withdrawal->updated_at->format('F j, Y g:i A') }}</td>
    </tr>
</table>

<h3>Expected Arrival Time</h3>
<p>Depending on your payment method, funds should arrive in your account within:</p>

<ul class="features-list">
    <li><strong>Crypto Wallet:</strong> 15-60 minutes</li>
    <li><strong>Bank Transfer:</strong> 1-5 business days</li>
    <li><strong>E-Wallet:</strong> 1-24 hours</li>
</ul>

<div class="text-center">
    <a href="{{ $settings->site_address }}/dashboard/withdrawals" class="button">View Transaction History</a>
</div>

<p>Thank you for using {{ $settings->site_name }}. We look forward to continuing to serve your investment needs.</p>

<p style="margin-top: 30px;">Best regards,<br><strong>The {{ $settings->site_name }} Team</strong></p>
@else
<h1>Withdrawal Request Received</h1>

<p>Hello {{ $user->name }},</p>
<p>Your withdrawal request has been successfully submitted and is currently being processed by our team.</p>

<div class="info-box">
    <p><strong>Processing Status: ⏳</strong> Your withdrawal is being reviewed.</p>
</div>

<h2>Transaction Details</h2>

<table class="details-table">
    <tr>
        <td>Amount</td>
        <td>{{ $user->currency }}{{ number_format($withdrawal->amount, 2) }}</td>
    </tr>
    <tr>
        <td>Payment Method</td>
        <td>{{ $withdrawal->payment_mode ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>Transaction ID</td>
        <td>#{{ $withdrawal->id }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>Processing</td>
    </tr>
    <tr>
        <td>Submitted</td>
        <td>{{ $withdrawal->created_at->format('F j, Y g:i A') }}</td>
    </tr>
</table>

<h2>What Happens Next?</h2>

<p>Our team is currently reviewing your withdrawal request. This process typically takes:</p>

<ul class="features-list">
    <li><strong>Standard Processing:</strong> 24-48 hours</li>
    <li><strong>Priority Processing:</strong> 4-12 hours (if applicable)</li>
</ul>

<p>You will receive a confirmation email once your withdrawal has been approved and processed.</p>

<div class="text-center">
    <a href="{{ $settings->site_address }}/user/withdrawals" class="button">Track Withdrawal Status</a>
</div>

<p>If you have any questions or need to cancel this request, please contact our support team immediately.</p>

<p style="margin-top: 30px;">Best regards,<br><strong>The {{ $settings->site_name }} Team</strong></p>
@endif
@endif

<div class="divider"></div>

<p class="text-muted" style="font-size: 13px;">For security reasons, please verify this transaction in your account dashboard. If you did not initiate this withdrawal, contact support immediately.</p>
@endsection
