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
        <div class="content">
            <div class="page-inner">
                <div class="mt-2 mb-4">
                    <h1 class="title1">{{ $title }}</h1>
                    <p class="text-muted">Comprehensive analytics for the copy trading system</p>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <!-- Actions Bar -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="{{ route('admin.copy-trading.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-period="7">7 Days</button>
                            <button type="button" class="btn btn-outline-primary" data-period="30">30 Days</button>
                            <button type="button" class="btn btn-outline-primary" data-period="90">90 Days</button>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3>${{ number_format($metrics['total_volume'] ?? 0, 0) }}</h3>
                                <small>Total Trading Volume</small>
                                <div class="mt-2">
                                    <small class="badge badge-light">
                                        {{ ($metrics['volume_change'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($metrics['volume_change'] ?? 0, 1) }}%
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>{{ $metrics['active_subscriptions'] ?? 0 }}</h3>
                                <small>Active Subscriptions</small>
                                <div class="mt-2">
                                    <small class="badge badge-light">
                                        {{ ($metrics['subscription_change'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($metrics['subscription_change'] ?? 0, 1) }}%
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3>{{ $metrics['total_trades'] ?? 0 }}</h3>
                                <small>Total Copy Trades</small>
                                <div class="mt-2">
                                    <small class="badge badge-light">
                                        {{ number_format($metrics['win_rate'] ?? 0, 1) }}% Win Rate
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-{{ ($metrics['total_pnl'] ?? 0) >= 0 ? 'warning' : 'danger' }} text-white">
                            <div class="card-body text-center">
                                <h3>${{ number_format($metrics['total_pnl'] ?? 0, 0) }}</h3>
                                <small>Total P&L</small>
                                <div class="mt-2">
                                    <small class="badge badge-light">
                                        {{ number_format($metrics['avg_roi'] ?? 0, 1) }}% Avg ROI
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <!-- Trading Volume Chart -->
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Trading Volume Over Time</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="volumeChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- P&L Distribution -->
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">P&L Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="pnlChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Analytics -->
                <div class="row mb-4">
                    <!-- Expert Traders Performance -->
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Top Expert Traders Performance</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Expert</th>
                                                <th>Subscribers</th>
                                                <th>Volume</th>
                                                <th>ROI</th>
                                                <th>Win Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($top_experts ?? [] as $expert)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($expert['avatar'])
                                                                <img src="{{ asset($expert['avatar']) }}" alt="{{ $expert['name'] }}" class="rounded-circle mr-2" width="30" height="30">
                                                            @else
                                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white mr-2" style="width: 30px; height: 30px; font-size: 12px;">
                                                                    {{ substr($expert['name'], 0, 1) }}
                                                                </div>
                                                            @endif
                                                            <strong>{{ $expert['name'] }}</strong>
                                                        </div>
                                                    </td>
                                                    <td>{{ $expert['subscribers'] }}</td>
                                                    <td>${{ number_format($expert['volume'], 0) }}</td>
                                                    <td class="text-{{ $expert['roi'] >= 0 ? 'success' : 'danger' }}">
                                                        {{ $expert['roi'] >= 0 ? '+' : '' }}{{ number_format($expert['roi'], 1) }}%
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar" role="progressbar" style="width: {{ $expert['win_rate'] }}%">
                                                                {{ number_format($expert['win_rate'], 1) }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3">
                                                        <small class="text-muted">No expert traders data available</small>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Trends -->
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Subscription Trends</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="subscriptionChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Analytics Tables -->
                <div class="row mb-4">
                    <!-- Most Traded Symbols -->
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Most Traded Symbols</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Symbol</th>
                                                <th>Trades</th>
                                                <th>Volume</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($top_symbols ?? [] as $symbol)
                                                <tr>
                                                    <td><strong>{{ $symbol['symbol'] }}</strong></td>
                                                    <td>{{ $symbol['trades'] }}</td>
                                                    <td>${{ number_format($symbol['volume'], 0) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-3">
                                                        <small class="text-muted">No trading data available</small>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Large Trades -->
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Large Copy Trades</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Time</th>
                                                <th>Expert</th>
                                                <th>Symbol</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>P&L</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($large_trades ?? [] as $trade)
                                                <tr>
                                                    <td><small>{{ $trade['created_at'] }}</small></td>
                                                    <td>{{ $trade['expert_name'] }}</td>
                                                    <td><strong>{{ $trade['symbol'] }}</strong></td>
                                                    <td>
                                                        <span class="badge badge-{{ $trade['trade_type'] === 'buy' ? 'success' : 'danger' }}">
                                                            {{ strtoupper($trade['trade_type']) }}
                                                        </span>
                                                    </td>
                                                    <td>${{ number_format($trade['amount'], 2) }}</td>
                                                    <td class="text-{{ $trade['pnl'] >= 0 ? 'success' : 'danger' }}">
                                                        {{ $trade['pnl'] >= 0 ? '+' : '' }}${{ number_format($trade['pnl'], 2) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-3">
                                                        <small class="text-muted">No large trades data available</small>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health Indicators -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">System Health & Performance</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-success">{{ number_format($health['uptime'] ?? 99.9, 1) }}%</h5>
                                            <small class="text-muted">System Uptime</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-info">{{ $health['avg_execution_time'] ?? '< 1s' }}</h5>
                                            <small class="text-muted">Avg Execution Time</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-warning">{{ $health['pending_jobs'] ?? 0 }}</h5>
                                            <small class="text-muted">Pending Jobs</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h5 class="text-{{ ($health['error_rate'] ?? 0) < 5 ? 'success' : 'danger' }}">{{ number_format($health['error_rate'] ?? 0, 1) }}%</h5>
                                            <small class="text-muted">Error Rate</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sample data - replace with actual data from controller
        const volumeData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Trading Volume ($)',
                data: [120000, 190000, 300000, 500000, 200000, 300000, 450000],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }]
        };

        const pnlData = {
            labels: ['Profitable', 'Break-even', 'Loss'],
            datasets: [{
                data: [65, 10, 25],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
            }]
        };

        const subscriptionData = {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'New Subscriptions',
                data: [12, 19, 3, 5],
                backgroundColor: 'rgba(54, 162, 235, 0.8)'
            }]
        };

        // Volume Chart
        const volumeCtx = document.getElementById('volumeChart').getContext('2d');
        new Chart(volumeCtx, {
            type: 'line',
            data: volumeData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // P&L Chart
        const pnlCtx = document.getElementById('pnlChart').getContext('2d');
        new Chart(pnlCtx, {
            type: 'doughnut',
            data: pnlData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Subscription Chart
        const subscriptionCtx = document.getElementById('subscriptionChart').getContext('2d');
        new Chart(subscriptionCtx, {
            type: 'bar',
            data: subscriptionData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Period selection
        document.querySelectorAll('[data-period]').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('[data-period]').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Here you would reload the data for the selected period
                const period = this.getAttribute('data-period');
                console.log('Loading data for period:', period);
            });
        });
    </script>
@endsection