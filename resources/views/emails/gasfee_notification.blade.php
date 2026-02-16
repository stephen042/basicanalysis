@extends('emails.layout')

@section('content')
    <div
        style="font-family: sans-serif; color: #333; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #3b82f6;">Top Up Payment Notification</h2>
        </div>

        @if ($isAdmin)
            <p><strong>Admin Alert:</strong> A user has reported a new top up payment.</p>
            <ul>
                <li><strong>User:</strong> {{ $user->name }} ({{ $user->email }})</li>
                <li><strong>Username:</strong> {{ $user->username }}</li>
                <li><strong>Amount Paid:</strong> {{ $amount }} XRP</li>
            </ul>
            <p>Please verify the transaction on the XRP Ledger.</p>
        @else
            <p>Hello {{ $user->name }},</p>
            <p>This is to confirm that we have received your notification for the top up payment of
                <strong>{{ $amount }} XRP</strong>.</p>
            <p>Our system is currently verifying the transaction. Your transaction will be processed immediately once confirmation is complete.</p>
        @endif

        <div style="margin-top: 30px; border-top: 1px solid #eee; pt: 10px; font-size: 12px; color: #888;">
            <p>This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </div>
@endsection
