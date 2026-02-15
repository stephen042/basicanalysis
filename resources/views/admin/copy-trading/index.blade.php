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
                    <p class="text-muted">Manage copy trading system, expert traders, and user subscriptions</p>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white shadow">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Experts</div>
                                        <div class="h5 mb-0 font-weight-bold">{{ $stats['total_experts'] }}</div>
                                        <small>{{ $stats['active_experts'] }} Active</small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white shadow">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Subscriptions</div>
                                        <div class="h5 mb-0 font-weight-bold">{{ $stats['total_subscriptions'] }}</div>
                                        <small>{{ $stats['active_subscriptions'] }} Active</small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-copy fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-info text-white shadow">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Volume</div>
                                        <div class="h5 mb-0 font-weight-bold">${{ number_format($stats['total_volume']) }}</div>
                                        <small>{{ $stats['total_copy_trades'] }} Copy Trades</small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-{{ $stats['total_pnl'] >= 0 ? 'success' : 'danger' }} text-white shadow">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total P&L</div>
                                        <div class="h5 mb-0 font-weight-bold">
                                            {{ $stats['total_pnl'] >= 0 ? '+' : '' }}${{ number_format($stats['total_pnl'], 2) }}
                                        </div>
                                        <small>{{ $stats['today_trades'] }} Trades Today</small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <a href="{{ route('admin.copy-trading.create-expert') }}" class="btn btn-primary btn-block">
                                            <i class="fas fa-plus mr-2"></i>Create Expert Trader
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="{{ route('admin.copy-trading.experts') }}" class="btn btn-info btn-block">
                                            <i class="fas fa-users mr-2"></i>Manage Experts
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="{{ route('admin.copy-trading.subscriptions') }}" class="btn btn-success btn-block">
                                            <i class="fas fa-copy mr-2"></i>View Subscriptions
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="{{ route('admin.copy-trading.analytics') }}" class="btn btn-warning btn-block">
                                            <i class="fas fa-chart-bar mr-2"></i>Analytics
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Experts -->
                <div class="row mb-4">
                    <div class="col-xl-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Copy Trading Activity</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>User</th>
                                                <th>Expert</th>
                                                <th>Asset</th>
                                                <th>Amount</th>
                                                <th>P&L</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentTrades as $trade)
                                                <tr>
                                                    <td>
                                                        <small>{{ $trade->created_at->format('M d, H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $trade->user->name }}</strong><br>
                                                        <small class="text-muted">{{ $trade->user->email }}</small>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $trade->copySubscription->expertTrader->name }}</strong><br>
                                                        <small class="text-muted">{{ $trade->copySubscription->expertTrader->specialization }}</small>
                                                    </td>
                                                    <td>
                                                        @if($trade->tradingAsset)
                                                            <strong>{{ $trade->tradingAsset->symbol }}</strong><br>
                                                            <small class="text-muted">{{ $trade->tradingAsset->name }}</small>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>${{ number_format($trade->amount, 2) }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="font-weight-bold text-{{ $trade->pnl >= 0 ? 'success' : 'danger' }}">
                                                            {{ $trade->pnl >= 0 ? '+' : '' }}${{ number_format($trade->pnl, 2) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $trade->type === 'profit' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($trade->type) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No recent trading activity</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('admin.copy-trading.logs') }}" class="btn btn-sm btn-outline-primary">
                                        View All Trading Logs
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Top Performing Experts</h6>
                            </div>
                            <div class="card-body">
                                @forelse($topExperts as $expert)
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                        <div class="mr-3">
                                            @if($expert->avatar)
                                                <img src="{{ asset($expert->avatar) }}" alt="{{ $expert->name }}" class="rounded-circle" width="40" height="40">
                                            @else
                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                                    {{ substr($expert->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $expert->name }}</h6>
                                            <small class="text-muted">{{ $expert->specialization }}</small>
                                            <div class="row mt-1">
                                                <div class="col-6">
                                                    <small class="text-success">{{ number_format($expert->current_roi, 1) }}% ROI</small>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-info">{{ number_format($expert->current_win_rate, 1) }}% Win Rate</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge badge-{{ $expert->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($expert->status) }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted text-center">No expert traders available</p>
                                @endforelse
                                <div class="text-center">
                                    <a href="{{ route('admin.copy-trading.experts') }}" class="btn btn-sm btn-outline-primary">
                                        Manage All Experts
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Trade Simulation -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Manual Trade Simulation</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.copy-trading.simulate-trade') }}" method="POST" class="row">
                                    @csrf
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="expert_trader_id">Expert Trader</label>
                                            <select name="expert_trader_id" class="form-control" required>
                                                <option value="">Select Expert</option>
                                                @foreach($topExperts as $expert)
                                                    <option value="{{ $expert->id }}">{{ $expert->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="amount">Amount ($)</label>
                                            <input type="number" name="amount" class="form-control" min="1" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="type">Trade Type</label>
                                            <select name="type" class="form-control" required>
                                                <option value="profit">Profit</option>
                                                <option value="loss">Loss</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="asset_id">Trading Asset (Optional)</label>
                                            <select name="asset_id" class="form-control">
                                                <option value="">Select Asset</option>
                                                <!-- Add trading assets here if needed -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-play mr-1"></i>Simulate
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection