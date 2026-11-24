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
    <div class="mt-2 mb-4"><h1 class="title1">{{ $title }}</h1><p class="text-muted">Review and settle open user trades.</p></div>
    <x-danger-alert /><x-success-alert />
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card"><div class="card-body table-responsive">
        <table class="table table-hover">
            <thead><tr><th>User</th><th>Instrument</th><th>Side</th><th>Amount</th><th>Leverage</th><th>Duration</th><th>Entry</th><th>Status</th><th>Opened</th><th>Result / Settle</th></tr></thead>
            <tbody>@forelse($trades as $trade)
                <tr>
                    <td>{{ $trade->user->name }}<br><small>{{ $trade->user->email }}</small></td>
                    <td>{{ $trade->instrument }}<br><small>{{ ucfirst($trade->category) }}</small></td>
                    <td>{{ $trade->direction }}</td><td>{{ $settings->currency }}{{ number_format($trade->amount,2) }}</td>
                    <td>{{ $trade->leverage }}x</td><td>{{ $trade->duration_minutes }} min</td>
                    <td>{{ $trade->entry_price ? number_format($trade->entry_price,8) : 'N/A' }}</td>
                    <td>{{ ucfirst($trade->status) }}</td><td>{{ $trade->created_at->format('M j, Y H:i') }}</td>
                    <td>@if($trade->status === 'active')
                        <form method="POST" action="{{ route('admin.manual-trading.settle', $trade) }}" class="d-flex align-items-center">
                            @csrf
                            <select name="result_type" class="form-control form-control-sm mr-1"><option value="profit">Profit</option><option value="loss">Loss</option></select>
                            <input name="result_amount" type="number" min="0" step="0.01" required class="form-control form-control-sm mr-1" placeholder="Amount">
                            <button class="btn btn-sm btn-primary">Complete</button>
                        </form>
                    @else {{ $settings->currency }}{{ number_format($trade->result_amount,2) }} @endif</td>
                </tr>
            @empty<tr><td colspan="10" class="text-center">No manual trades recorded.</td></tr>@endforelse</tbody>
        </table>
        {{ $trades->links() }}
    </div></div>
</div></div></div>
@endsection
