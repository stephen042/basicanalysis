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
    <div class="main-panel">
        <div class="content ">
            <div class="page-inner">
                <div class="mt-2 mb-4">
                    <h1 class="title1 ">{{ $title }}</h1>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <div class="mb-3 row">
                    <div class="col">
                        <a href="{{ route('admin.trading-bots.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back to Trading Bots
                        </a>
                        <a href="{{ route('admin.trading-bots.logs') }}" class="btn btn-warning">
                            <i class="fa fa-chart-line"></i> View Trading Logs
                        </a>
                    </div>
                </div>

                <div class="mb-5 row">
                    <div class="col card p-3 shadow ">
                        <div class="bs-example widget-shadow table-responsive" data-example-id="hoverable-table">
                            <span style="margin:3px;">
                                <table id="ShipTable" class="table table-hover ">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>User</th>
                                            <th>Trading Bot</th>
                                            <th>Investment Amount</th>
                                            <th>Status</th>
                                            <th>Started</th>
                                            <th>Expires</th>
                                            <th>Profit/Loss</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subscribers as $subscription)
                                            <tr>
                                                <th scope="row">{{ $subscription->id }}</th>
                                                <td>
                                                    <strong>{{ $subscription->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $subscription->user->email }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $subscription->tradingBot->name }}</strong><br>
                                                    <small class="text-muted">{{ $subscription->tradingBot->profit_rate }}% expected</small>
                                                </td>
                                                <td>${{ number_format($subscription->amount) }}</td>
                                                <td>
                                                    @if ($subscription->status == 'active')
                                                        <span class="badge badge-success">{{ ucfirst($subscription->status) }}</span>
                                                    @elseif ($subscription->status == 'completed')
                                                        <span class="badge badge-info">{{ ucfirst($subscription->status) }}</span>
                                                    @else
                                                        <span class="badge badge-danger">{{ ucfirst($subscription->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($subscription->created_at)->toDayDateTimeString() }}</td>
                                                <td>
                                                    @if($subscription->status === 'active')
                                                        {{ \Carbon\Carbon::parse($subscription->expires_at)->toDayDateTimeString() }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $totalProfit = $subscription->tradingLogs->where('type', 'profit')->sum('amount');
                                                        $totalLoss = $subscription->tradingLogs->where('type', 'loss')->sum('amount');
                                                        $netProfit = $totalProfit - $totalLoss;
                                                    @endphp
                                                    
                                                    @if($netProfit > 0)
                                                        <span class="text-success">+${{ number_format($netProfit, 2) }}</span>
                                                    @elseif($netProfit < 0)
                                                        <span class="text-danger">-${{ number_format(abs($netProfit), 2) }}</span>
                                                    @else
                                                        <span class="text-muted">$0.00</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($subscription->tradingLogs->count() > 0)
                                                        <button class="btn btn-sm btn-info" onclick="showLogs({{ $subscription->id }})">
                                                            <i class="fa fa-eye"></i> View Logs
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Trading Logs -->
    <div class="modal fade" id="logsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Trading Logs</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="logsContent">
                    <!-- Logs will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
    function showLogs(subscriptionId) {
        // Find subscription data
        let subscription = @json($subscribers).find(s => s.id === subscriptionId);
        
        let content = '<div class="table-responsive">';
        content += '<table class="table table-sm">';
        content += '<thead><tr><th>Type</th><th>Amount</th><th>Date</th></tr></thead>';
        content += '<tbody>';
        
        subscription.trading_logs.forEach(log => {
            let badgeClass = log.type === 'profit' ? 'badge-success' : 'badge-danger';
            let icon = log.type === 'profit' ? 'fa-arrow-up' : 'fa-arrow-down';
            
            content += `<tr>
                <td><span class="badge ${badgeClass}"><i class="fa ${icon}"></i> ${log.type.charAt(0).toUpperCase() + log.type.slice(1)}</span></td>
                <td>$${parseFloat(log.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td>${new Date(log.created_at).toLocaleString()}</td>
            </tr>`;
        });
        
        content += '</tbody></table></div>';
        
        document.getElementById('logsContent').innerHTML = content;
        $('#logsModal').modal('show');
    }
    </script>
@endsection
