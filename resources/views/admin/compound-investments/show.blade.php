<?php
if (Auth('admin')->User()->dashboard_style == 'light') {
    $text = 'dark';
} else {
    $text = 'light';
}
$lastTopup = $investment->topups->last();
$anchor = $lastTopup
    ? $lastTopup->created_at->copy()
    : ($investment->start_date
        ? $investment->start_date->copy()
        : ($investment->approved_at ? $investment->approved_at->copy() : ($investment->status === 'active' ? $investment->created_at->copy() : null)));
$nextDue = null;
if ($anchor) {
    if ($investment->recurring_frequency === 'daily') {
        $nextDue = $anchor->addDay();
    } elseif ($investment->recurring_frequency === 'weekly') {
        $nextDue = $anchor->addWeek();
    } else {
        $nextDue = $anchor->addMonth();
    }
}
$topupAllowed = $investment->status === 'active'
    && $investment->deposit_status === 'approved';
?>
@extends('layouts.app')
@section('content')
@include('admin.topmenu')
@include('admin.sidebar')
<div class="main-panel"><div class="content"><div class="page-inner">
    <div class="mt-2 mb-4 d-flex flex-wrap justify-content-between align-items-center">
        <div><h1 class="title1">{{ $title }} #{{ $investment->id }}</h1><p class="text-muted">Payment review, top-up management, and completion are recorded in an audit trail.</p></div>
        <a href="{{ route('admin.compound-investments.index') }}" class="btn btn-outline-primary">Back to investments</a>
    </div>
    <x-danger-alert /><x-success-alert />
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card"><div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h3>{{ $investment->user->name }} <small class="text-muted">{{ $investment->user->email }}</small></h3>
                <div class="row mt-4">
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Initial amount</b><br>{{ $currency }}{{ number_format($investment->amount,2) }}</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Requested</b><br>{{ $investment->created_at->format('M j, Y H:i') }}</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Payment method</b><br>{{ $investment->payment_method ?: '—' }}</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Duration</b><br>{{ $investment->duration_months }} months</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Monthly return</b><br>{{ number_format($investment->monthly_return,2) }}%</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Performance rate</b><br>{{ number_format($investment->performance_rate,2) }}%</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Recurring frequency</b><br>{{ ucfirst($investment->recurring_frequency) }}</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Expected top-up</b><br>{{ $currency }}{{ number_format($investment->recurring_amount,2) }}</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Payment status</b><br>{{ $investment->deposit_status === 'approved' ? 'Approved' : ($investment->deposit_status === 'rejected' ? 'Rejected' : ($investment->payment_proof ? 'Awaiting admin approval' : 'Pending payment')) }}</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Investment status</b><br>{{ $investment->display_status }}</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Start date</b><br>{{ $investment->start_date ? $investment->start_date->format('M j, Y H:i') : 'Not started' }}</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Expected completion</b><br>{{ $investment->expected_completion_at ? $investment->expected_completion_at->format('M j, Y H:i') : 'Set after approval' }}</div>
                    <div class="col-sm-6 col-lg-4 mb-3"><b>Total earnings credited</b><br>{{ $currency }}{{ number_format($investment->total_earnings,2) }}</div>
                    @if($investment->completed_at)<div class="col-sm-6 col-lg-4 mb-3"><b>Completed</b><br>{{ $investment->completed_at->format('M j, Y H:i') }}</div>@endif
                    @if($investment->rejection_reason)<div class="col-12 mb-3"><b>Rejection note</b><br>{{ $investment->rejection_reason }}</div>@endif
                </div>
                @if($investment->payment_proof)
                    <a class="btn btn-outline-info" target="_blank" rel="noopener" href="{{ \Illuminate\Support\Facades\Storage::url($investment->payment_proof) }}">Open payment receipt</a>
                @else
                    <div class="alert alert-warning">No payment receipt has been uploaded yet. Do not approve until payment is verified.</div>
                @endif
                @if($investment->payment_proof && !$isUsdtPayment)
                    <div class="alert alert-danger mt-3">This receipt is associated with a non-USDT method. Compound investment approval is USDT-only; reject this request and do not activate it.</div>
                @endif
            </div>
            <div class="col-md-4">
                <div class="rounded border p-3">
                    <h5>Configured payment instructions</h5>
                    <p><b>Method:</b> {{ $investment->payment_method ?: '—' }}</p>
                    @if(!empty($payment['address']))<p><b>Address:</b><br><code class="text-break">{{ $payment['address'] }}</code></p>@endif
                    @if(!empty($payment['network']))<p><b>Network:</b> {{ $payment['network'] }}</p>@endif
                    @if(!empty($payment['bank']))<p><b>Bank:</b> {{ $payment['bank'] }}</p>@endif
                    @if(!empty($payment['account_name']))<p><b>Account:</b> {{ $payment['account_name'] }}</p>@endif
                    @if(!empty($payment['account_number']))<p><b>Account number:</b> {{ $payment['account_number'] }}</p>@endif
                    @if(!empty($payment['swift']))<p><b>SWIFT / code:</b> {{ $payment['swift'] }}</p>@endif
                    @if(!empty($payment['instructions']))<p><b>Admin instructions:</b><br>{{ $payment['instructions'] }}</p>@endif
                    @if(!empty($payment['barcode']))<img src="{{ $payment['barcode'] }}" alt="Configured payment QR" class="img-fluid mt-2" style="max-width:240px">
                    @elseif(!empty($payment['address']))<img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($payment['address']) }}" alt="Payment wallet QR" class="img-fluid mt-2" style="max-width:240px">@endif
                </div>
            </div>
        </div>
    </div></div>

    @if($investment->status === 'pending')
        <div class="card"><div class="card-body">
            <h4>Review initial payment</h4>
            <p class="text-muted">Approval starts the duration clock and sets an expected completion date. Approval is only allowed after a receipt has been submitted.</p>
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('admin.compound-investments.decision', $investment) }}">@csrf
                        <input type="hidden" name="decision" value="approve">
                        <button class="btn btn-success btn-block" {{ !$investment->payment_proof || !$isUsdtPayment ? 'disabled' : '' }}>Approve USDT Payment &amp; Activate</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form method="POST" action="{{ route('admin.compound-investments.decision', $investment) }}" class="d-flex">@csrf
                        <input type="hidden" name="decision" value="reject">
                        <input type="text" name="rejection_reason" maxlength="1000" class="form-control mr-2" placeholder="Optional rejection reason">
                        <button class="btn btn-danger">Deny</button>
                    </form>
                </div>
            </div>
        </div></div>
    @elseif($investment->status === 'active')
        <div class="card"><div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <div><h4>Recurring top-up management</h4><p class="text-muted">The schedule is an informational reminder. Admins may top up an active investment before, on, or after the expected date.</p></div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6"><div class="rounded border p-3"><b>Next Expected Top-Up</b><br>Expected date: {{ $nextDue ? $nextDue->format('M j, Y H:i') : 'Not available' }}<br>Expected amount: {{ $currency }}{{ number_format($investment->recurring_amount,2) }}</div></div>
                <div class="col-md-6"><div class="rounded border p-3"><b>Actual payment</b><br>The admin-entered amount and current processing time are recorded on each top-up.</div></div>
            </div>
            @if($topupAllowed)
                <form method="POST" action="{{ route('admin.compound-investments.topup', $investment) }}" class="row align-items-end mt-3">@csrf
                    <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                    <div class="col-md-4"><label>Actual amount to credit ({{ $currency }})</label><input type="number" min="0.01" max="1000000000" step="0.01" name="amount" required value="{{ number_format($investment->recurring_amount,2,'.','') }}" class="form-control"></div>
                    <div class="col-md-5"><label>Admin note (optional)</label><input type="text" name="note" maxlength="255" class="form-control" placeholder="Top-up period or reference"></div>
                    <div class="col-md-3"><button class="btn btn-primary btn-block">Top Up Now</button></div>
                </form>
            @else
                <div class="alert alert-warning mt-3">Top-ups are available after initial payment approval and activation. Rejected or completed investments cannot be topped up.</div>
            @endif
            <hr>
            <p><b>Manual completion:</b> Authorized admins may complete early. Completion does not credit the initial principal or earnings again.</p>
            <form method="POST" action="{{ route('admin.compound-investments.complete', $investment) }}" onsubmit="return confirm('Complete this investment? No additional balance will be credited.');">@csrf
                <input type="hidden" name="manual_override" value="1">
                <button class="btn btn-secondary">Complete Investment</button>
            </form>
        </div></div>
    @endif

    <div class="card"><div class="card-body">
        <h4>Top-up audit history</h4>
        <div class="table-responsive"><table class="table table-hover">
            <thead><tr><th>Amount</th><th>Recorded</th><th>Admin</th><th>Idempotency reference</th><th>Note</th></tr></thead>
            <tbody>
            @forelse($investment->topups as $topup)
                <tr><td>{{ $currency }}{{ number_format($topup->amount,2) }}</td><td>{{ $topup->created_at->format('M j, Y H:i') }}</td><td>{{ $topup->admin ? $topup->admin->firstName : 'Admin' }}</td><td>{{ $topup->idempotency_key ?: 'Legacy' }}</td><td>{{ $topup->note ?: '—' }}</td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted">No top-ups recorded.</td></tr>@endforelse
            </tbody>
        </table></div>
    </div></div>
</div></div></div>
@endsection
