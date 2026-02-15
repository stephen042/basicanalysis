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
                    <p class="text-muted">Monitor copy trading system logs and activities</p>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <!-- Actions Bar -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <a href="{{ route('admin.copy-trading.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="logType">
                            <option value="">All Log Types</option>
                            <option value="trade_executed">Trade Executed</option>
                            <option value="subscription_created">Subscription Created</option>
                            <option value="subscription_paused">Subscription Paused</option>
                            <option value="subscription_cancelled">Subscription Cancelled</option>
                            <option value="expert_trade_generated">Expert Trade Generated</option>
                            <option value="error">Errors</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search logs..." id="searchInput">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4>{{ $stats['today_logs'] ?? 0 }}</h4>
                                <small>Today's Logs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4>{{ $stats['successful_trades'] ?? 0 }}</h4>
                                <small>Successful Trades</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4>{{ $stats['errors'] ?? 0 }}</h4>
                                <small>Errors</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4>{{ $stats['active_subscriptions'] ?? 0 }}</h4>
                                <small>Active Subscriptions</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Copy Trading System Logs</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="10%">Time</th>
                                                <th width="12%">Type</th>
                                                <th width="15%">User/Expert</th>
                                                <th width="40%">Message</th>
                                                <th width="13%">Details</th>
                                                <th width="10%">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($logs as $log)
                                                <tr class="log-row" data-type="{{ $log['type'] }}">
                                                    <td>
                                                        <small class="text-muted">{{ $log['created_at'] }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $log['type_color'] }}">
                                                            {{ $log['type_label'] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($log['user'])
                                                            <div>
                                                                <strong>{{ $log['user']['name'] }}</strong><br>
                                                                <small class="text-muted">{{ $log['user']['email'] }}</small>
                                                            </div>
                                                        @elseif($log['expert'])
                                                            <div>
                                                                <strong>{{ $log['expert']['name'] }}</strong><br>
                                                                <small class="text-muted">Expert Trader</small>
                                                            </div>
                                                        @else
                                                            <small class="text-muted">System</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>
                                                            {{ $log['message'] }}
                                                            @if($log['sub_message'])
                                                                <br><small class="text-muted">{{ $log['sub_message'] }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($log['amount'])
                                                            <strong>${{ number_format($log['amount'], 2) }}</strong><br>
                                                        @endif
                                                        @if($log['symbol'])
                                                            <span class="badge badge-secondary">{{ $log['symbol'] }}</span><br>
                                                        @endif
                                                        @if($log['trade_type'])
                                                            <small class="text-{{ $log['trade_type'] === 'buy' ? 'success' : 'danger' }}">{{ strtoupper($log['trade_type']) }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $log['status_color'] }}">
                                                            <i class="fas fa-{{ $log['status_icon'] }}"></i>
                                                            {{ $log['status'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="fas fa-list-alt fa-3x mb-3"></i>
                                                            <h5>No Logs Found</h5>
                                                            <p>No copy trading logs available for the selected criteria.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if(isset($logs) && is_object($logs) && method_exists($logs, 'hasPages') && $logs->hasPages())
                                <div class="card-footer">
                                    {{ $logs->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Timeline -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Activity Timeline</h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    @forelse($recent_activities as $activity)
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-{{ $activity['color'] }}">
                                                <i class="fas fa-{{ $activity['icon'] }}"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">{{ $activity['title'] }}</h6>
                                                <p class="timeline-text">{{ $activity['description'] }}</p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> {{ $activity['time'] }}
                                                </small>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted">
                                            <i class="fas fa-clock fa-2x mb-2"></i>
                                            <p>No recent activity</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline:before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-marker {
            position: absolute;
            left: -22px;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #007bff;
        }

        .timeline-title {
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 600;
        }

        .timeline-text {
            margin-bottom: 10px;
            font-size: 13px;
            color: #6c757d;
        }
    </style>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.log-row');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter by log type
        document.getElementById('logType').addEventListener('change', function() {
            const selectedType = this.value;
            const tableRows = document.querySelectorAll('.log-row');
            
            tableRows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                if (selectedType === '' || rowType === selectedType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Auto-refresh logs every 30 seconds
        setInterval(function() {
            // You can implement auto-refresh functionality here
            console.log('Auto-refreshing logs...');
        }, 30000);
    </script>
@endsection