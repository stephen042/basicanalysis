<?php
if (Auth('admin')->User()->dashboard_style == 'light') {
    $text = 'dark';
    $bg = 'light';
} else {
    $text = 'light';
    $bg = 'dark';
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
                    <h1 class="title1">{{ $user->name }}'s Trading Bots</h1>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <!-- Page Header -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <a href="{{ route('viewuser', $user->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to User Details
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="card p-3 shadow">
                            <div class="d-flex align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center text-primary">
                                        <i class="fas fa-robot fa-3x"></i>
                                    </div>
                                </div>
                                <div class="col ml-3">
                                    <p class="mb-1 text-muted">Active Bots</p>
                                    <h3 class="mb-0">{{ $activeBotsCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card p-3 shadow">
                            <div class="d-flex align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center text-success">
                                        <i class="fas fa-check-circle fa-3x"></i>
                                    </div>
                                </div>
                                <div class="col ml-3">
                                    <p class="mb-1 text-muted">Completed</p>
                                    <h3 class="mb-0">{{ $completedBotsCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card p-3 shadow">
                            <div class="d-flex align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center text-info">
                                        <i class="fas fa-dollar-sign fa-3x"></i>
                                    </div>
                                </div>
                                <div class="col ml-3">
                                    <p class="mb-1 text-muted">Total Invested</p>
                                    <h3 class="mb-0">{{ $user->currency }}{{ number_format($totalInvested, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card p-3 shadow">
                            <div class="d-flex align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center text-warning">
                                        <i class="fas fa-chart-line fa-3x"></i>
                                    </div>
                                </div>
                                <div class="col ml-3">
                                    <p class="mb-1 text-muted">Total Profit</p>
                                    <h3 class="mb-0 text-success">{{ $user->currency }}{{ number_format($totalReturns, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trading Bots List -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card p-3 shadow">
                            <div class="card-header">
                                <h4 class="card-title">Trading Bot History</h4>
                            </div>
                            <div class="card-body">
                                @if($userTradingBots->count() > 0)
                                    <div class="table-responsive">
                                        <table id="ShipTable" class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Bot Name</th>
                                                    <th>Amount</th>
                                                    <th>Duration</th>
                                                    <th>Profit Rate</th>
                                                    <th>Status</th>
                                                    <th>Started</th>
                                                    <th>Expires At</th>
                                                    <th>Profit/Loss</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($userTradingBots as $userBot)
                                                    <tr>
                                                        <th scope="row">{{ $userBot->id }}</th>
                                                        <td>
                                                            <strong>{{ $userBot->tradingBot->name }}</strong><br>
                                                            <small class="text-muted">{{ $userBot->tradingBot->description }}</small>
                                                        </td>
                                                        <td>{{ $user->currency }}{{ number_format($userBot->amount, 2) }}</td>
                                                        <td>{{ $userBot->tradingBot->duration }} hours</td>
                                                        <td><span class="badge badge-info">{{ $userBot->tradingBot->profit_rate }}%</span></td>
                                                        <td>
                                                            @if($userBot->status == 'active')
                                                                <span class="badge badge-success">Active</span>
                                                            @elseif($userBot->status == 'completed')
                                                                <span class="badge badge-primary">Completed</span>
                                                            @elseif($userBot->status == 'cancelled')
                                                                <span class="badge badge-danger">Cancelled</span>
                                                            @else
                                                                <span class="badge badge-secondary">{{ ucfirst($userBot->status) }}</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($userBot->created_at)->toDayDateTimeString() }}</td>
                                                        <td>
                                                            @if($userBot->status === 'active')
                                                                {{ \Carbon\Carbon::parse($userBot->expires_at)->toDayDateTimeString() }}
                                                            @else
                                                                <span class="text-danger">Expired</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $totalProfit = $userBot->tradingLogs->where('type', 'profit')->sum('amount');
                                                                $totalLoss = $userBot->tradingLogs->where('type', 'loss')->sum('amount');
                                                                $netProfit = $totalProfit - $totalLoss;
                                                            @endphp
                                                            @if($netProfit > 0)
                                                                <span class="text-success font-weight-bold">+{{ $user->currency }}{{ number_format($netProfit, 2) }}</span>
                                                            @elseif($netProfit < 0)
                                                                <span class="text-danger font-weight-bold">-{{ $user->currency }}{{ number_format(abs($netProfit), 2) }}</span>
                                                            @else
                                                                <span class="text-muted">{{ $user->currency }}0.00</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="#" class="m-1 btn btn-info btn-sm" 
                                                               data-toggle="modal" 
                                                               data-target="#botDetailsModal{{ $userBot->id }}"
                                                               title="View Details">
                                                                <i class="fa fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="mt-3">
                                        {{ $userTradingBots->links() }}
                                    </div>

                                    <!-- All Modals (Outside Table) -->
                                    @foreach($userTradingBots as $userBot)
                                    <div class="modal fade" id="botDetailsModal{{ $userBot->id }}" tabindex="-1" role="dialog">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-primary text-white">
                                                                    <h5 class="modal-title">
                                                                        <i class="fa fa-robot"></i> {{ $userBot->tradingBot->name }} - Details
                                                                    </h5>
                                                                    <button type="button" class="close text-white" data-dismiss="modal">
                                                                        <span>&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <h6 class="text-primary"><i class="fa fa-info-circle"></i> Bot Information</h6>
                                                                            <table class="table table-bordered table-sm">
                                                                                <tr>
                                                                                    <td><strong>Bot Name:</strong></td>
                                                                                    <td>{{ $userBot->tradingBot->name }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Description:</strong></td>
                                                                                    <td>{{ $userBot->tradingBot->description }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Investment:</strong></td>
                                                                                    <td><strong class="text-info">{{ $user->currency }}{{ number_format($userBot->amount, 2) }}</strong></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Duration:</strong></td>
                                                                                    <td>{{ $userBot->tradingBot->duration }} hours</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Profit Rate:</strong></td>
                                                                                    <td><span class="badge badge-success">{{ $userBot->tradingBot->profit_rate }}%</span></td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <h6 class="text-primary"><i class="fa fa-chart-bar"></i> Trading Summary</h6>
                                                                            <table class="table table-bordered table-sm">
                                                                                <tr>
                                                                                    <td><strong>Status:</strong></td>
                                                                                    <td>
                                                                                        @if($userBot->status == 'active')
                                                                                            <span class="badge badge-success">Active</span>
                                                                                        @elseif($userBot->status == 'completed')
                                                                                            <span class="badge badge-primary">Completed</span>
                                                                                        @else
                                                                                            <span class="badge badge-danger">{{ ucfirst($userBot->status) }}</span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Started:</strong></td>
                                                                                    <td>{{ \Carbon\Carbon::parse($userBot->created_at)->toDayDateTimeString() }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Expires At:</strong></td>
                                                                                    <td>
                                                                                        @if($userBot->status === 'active')
                                                                                            {{ \Carbon\Carbon::parse($userBot->expires_at)->toDayDateTimeString() }}
                                                                                        @else
                                                                                            <span class="text-muted">N/A</span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Total Trades:</strong></td>
                                                                                    <td><span class="badge badge-info">{{ $userBot->tradingLogs->count() }}</span></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Net Profit:</strong></td>
                                                                                    <td>
                                                                                        @php
                                                                                            $modalProfit = $userBot->tradingLogs->where('type', 'profit')->sum('amount');
                                                                                            $modalLoss = $userBot->tradingLogs->where('type', 'loss')->sum('amount');
                                                                                            $modalNet = $modalProfit - $modalLoss;
                                                                                        @endphp
                                                                                        <strong class="{{ $modalNet > 0 ? 'text-success' : 'text-danger' }}">
                                                                                            {{ $modalNet > 0 ? '+' : '' }}{{ $user->currency }}{{ number_format($modalNet, 2) }}
                                                                                        </strong>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>

                                                                    @if($userBot->tradingLogs->count() > 0)
                                                                        <hr>
                                                                        <h6 class="text-primary"><i class="fa fa-history"></i> Recent Trading Activity</h6>
                                                                        <div class="table-responsive">
                                                                            <table class="table table-hover table-sm">
                                                                                <thead class="thead-light">
                                                                                    <tr>
                                                                                        <th>Date</th>
                                                                                        <th>Type</th>
                                                                                        <th>Asset</th>
                                                                                        <th>Amount</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach($userBot->tradingLogs->sortByDesc('created_at')->take(10) as $log)
                                                                                        <tr>
                                                                                            <td><small>{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y H:i A') }}</small></td>
                                                                                            <td>
                                                                                                @if($log->type == 'profit')
                                                                                                    <span class="badge badge-success">
                                                                                                        <i class="fa fa-arrow-up"></i> Profit
                                                                                                    </span>
                                                                                                @else
                                                                                                    <span class="badge badge-danger">
                                                                                                        <i class="fa fa-arrow-down"></i> Loss
                                                                                                    </span>
                                                                                                @endif
                                                                                            </td>
                                                                                            <td>
                                                                                                @if($log->tradingAsset)
                                                                                                    <span class="badge badge-info">{{ $log->tradingAsset->symbol }}</span>
                                                                                                    {{ $log->tradingAsset->name }}
                                                                                                @else
                                                                                                    <span class="text-muted">N/A</span>
                                                                                                @endif
                                                                                            </td>
                                                                                            <td>
                                                                                                <strong class="{{ $log->type == 'profit' ? 'text-success' : 'text-danger' }}">
                                                                                                    {{ $log->type == 'profit' ? '+' : '-' }}{{ $user->currency }}{{ number_format($log->amount, 2) }}
                                                                                                </strong>
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    @else
                                                                        <div class="alert alert-info">
                                                                            <i class="fa fa-info-circle"></i> No trading activity recorded yet for this bot.
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                        <i class="fa fa-times"></i> Close
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                    @endforeach

                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-robot fa-4x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Trading Bots</h5>
                                        <p class="text-muted">This user hasn't subscribed to any trading bots yet.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
