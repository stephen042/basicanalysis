<?php
if (Auth('admin')->User()->dashboard_style == 'light') {
    $text = 'dark';
} else {
    $text = 'light';
}
?>
@extends('layouts.app')
@section('content')
@include('admin.topmenu')
@include('admin.sidebar')
<div class="main-panel"><div class="content"><div class="page-inner">
    <div class="mt-2 mb-4"><h1 class="title1">{{ $title }}</h1><p class="text-muted">Review initial deposits and manage approved compound investments.</p></div>
    <x-danger-alert /><x-success-alert />
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card"><div class="card-body table-responsive">
        <table class="table table-hover">
            <thead><tr><th>User</th><th>Initial amount</th><th>Request date</th><th>Frequency / top-up</th><th>Pending contributions</th><th>Payment</th><th>Investment</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($investments as $investment)
                <tr>
                    <td>{{ $investment->user->name }}<small class="d-block text-muted">{{ $investment->user->email }}</small></td>
                    <td>{{ $currency }}{{ number_format($investment->amount,2) }}</td>
                    <td>{{ $investment->created_at->format('M j, Y H:i') }}</td>
                    <td>{{ ucfirst($investment->recurring_frequency) }}<small class="d-block text-muted">{{ $currency }}{{ number_format($investment->recurring_amount,2) }}</small></td>
                    <td>
                        @php($pendingContributionCount = $investment->relationLoaded('contributions') ? $investment->contributions->filter(function ($contribution) { return $contribution->status === 'pending' && ($contribution->deposit_id || $contribution->balance_debited); })->count() : 0)
                        @if($pendingContributionCount > 0)
                            <span class="badge badge-warning">{{ $pendingContributionCount }} awaiting review</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $investment->deposit_status === 'approved' ? 'success' : ($investment->deposit_status === 'rejected' ? 'danger' : ($investment->payment_proof ? 'info' : 'warning')) }}">
                            {{ $investment->deposit_status === 'approved' ? 'Approved' : ($investment->deposit_status === 'rejected' ? 'Rejected' : (($investment->payment_source === 'subscription_balance' || $investment->payment_method === 'Subscription Balance') ? 'Balance reserved · Pending admin approval' : ($investment->payment_proof ? 'Receipt submitted' : 'Pending payment'))) }}
                        </span>
                        <small class="d-block text-muted">{{ $investment->payment_method ?: '—' }}</small>
                    </td>
                    <td><span class="badge badge-{{ $investment->status === 'active' ? 'success' : ($investment->status === 'completed' ? 'secondary' : ($investment->status === 'rejected' ? 'danger' : 'warning')) }}">{{ ucfirst($investment->status) }}</span></td>
                    <td><a class="btn btn-sm btn-primary" href="{{ route('admin.compound-investments.show', $investment) }}">Review / Manage</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No compound investment requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $investments->links() }}
    </div></div>
</div></div></div>
@endsection
