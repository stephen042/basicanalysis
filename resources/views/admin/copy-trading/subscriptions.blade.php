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
                    <p class="text-muted">Monitor and manage all copy trading subscriptions</p>
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
                        <div class="input-group" style="max-width: 300px; margin-left: auto;">
                            <input type="text" class="form-control" placeholder="Search subscriptions..." id="searchInput">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Copy Trading Subscriptions ({{ $subscriptions->total() }})</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>User</th>
                                                <th>Expert Trader</th>
                                                <th>Amount</th>
                                                <th>Copy %</th>
                                                <th>P&L</th>
                                                <th>ROI</th>
                                                <th>Trades</th>
                                                <th>Status</th>
                                                <th>Duration</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($subscriptions as $subscription)
                                                <tr>
                                                    <td>
                                                        <strong>#{{ $subscription->id }}</strong>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $subscription->user->name }}</strong><br>
                                                            <small class="text-muted">{{ $subscription->user->email }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-2">
                                                                @if($subscription->expertTrader->avatar)
                                                                    <img src="{{ asset($subscription->expertTrader->avatar) }}" alt="{{ $subscription->expertTrader->name }}" class="rounded-circle" width="30" height="30">
                                                                @else
                                                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 30px; height: 30px; font-size: 12px;">
                                                                        {{ substr($subscription->expertTrader->name, 0, 1) }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <strong>{{ $subscription->expertTrader->name }}</strong><br>
                                                                <small class="text-muted">{{ $subscription->expertTrader->specialization }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong>${{ number_format($subscription->amount, 2) }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $subscription->copy_percentage }}%</span>
                                                    </td>
                                                    <td>
                                                        <span class="font-weight-bold text-{{ $subscription->total_pnl >= 0 ? 'success' : 'danger' }}">
                                                            {{ $subscription->total_pnl >= 0 ? '+' : '' }}${{ number_format($subscription->total_pnl, 2) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="font-weight-bold text-{{ $subscription->current_roi >= 0 ? 'success' : 'danger' }}">
                                                            {{ $subscription->current_roi >= 0 ? '+' : '' }}{{ number_format($subscription->current_roi, 2) }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="text-center">
                                                            <strong>{{ $subscription->total_trades }}</strong><br>
                                                            <small class="text-success">{{ $subscription->winning_trades }} wins</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'paused' ? 'warning' : ($subscription->status === 'completed' ? 'info' : 'secondary')) }}">
                                                            {{ ucfirst($subscription->status) }}
                                                        </span>
                                                        @if($subscription->status === 'active' && $subscription->isExpired())
                                                            <br><small class="text-danger">Expired</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="text-center">
                                                            <small class="text-muted">Started:</small><br>
                                                            <small>{{ $subscription->started_at->format('M d, Y') }}</small><br>
                                                            @if($subscription->status === 'active')
                                                                <small class="text-muted">{{ $subscription->getDaysRemaining() }} days left</small>
                                                            @else
                                                                <small class="text-muted">{{ $subscription->expires_at->format('M d, Y') }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-info" onclick="viewSubscriptionDetails({{ $subscription->id }})" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @if($subscription->status === 'active')
                                                                <button class="btn btn-sm btn-outline-warning" onclick="pauseSubscription({{ $subscription->id }})" title="Pause">
                                                                    <i class="fas fa-pause"></i>
                                                                </button>
                                                            @elseif($subscription->status === 'paused')
                                                                <button class="btn btn-sm btn-outline-success" onclick="resumeSubscription({{ $subscription->id }})" title="Resume">
                                                                    <i class="fas fa-play"></i>
                                                                </button>
                                                            @endif
                                                            @if(in_array($subscription->status, ['active', 'paused']))
                                                                <button class="btn btn-sm btn-outline-danger" onclick="cancelSubscription({{ $subscription->id }})" title="Cancel">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="fas fa-copy fa-3x mb-3"></i>
                                                            <h5>No Copy Trading Subscriptions Found</h5>
                                                            <p>Users haven't started any copy trading subscriptions yet.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if($subscriptions->hasPages())
                                <div class="card-footer">
                                    {{ $subscriptions->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Subscription Statistics -->
                @if($subscriptions->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $subscriptions->where('status', 'active')->count() }}</h4>
                                    <small>Active Subscriptions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>${{ number_format($subscriptions->sum('amount'), 0) }}</h4>
                                    <small>Total Volume</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $subscriptions->sum('total_trades') }}</h4>
                                    <small>Total Copy Trades</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-{{ $subscriptions->sum('total_pnl') >= 0 ? 'warning' : 'danger' }} text-white">
                                <div class="card-body text-center">
                                    <h4>${{ number_format($subscriptions->sum('total_pnl'), 0) }}</h4>
                                    <small>Total P&L</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Subscription Details Modal -->
    <div class="modal fade" id="subscriptionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subscription Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="subscriptionDetails">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function viewSubscriptionDetails(id) {
            // You can implement AJAX call to load subscription details
            $('#subscriptionModal').modal('show');
        }

        function pauseSubscription(id) {
            if (confirm('Are you sure you want to pause this subscription?')) {
                // Implement pause functionality
                console.log('Pausing subscription:', id);
            }
        }

        function resumeSubscription(id) {
            if (confirm('Are you sure you want to resume this subscription?')) {
                // Implement resume functionality
                console.log('Resuming subscription:', id);
            }
        }

        function cancelSubscription(id) {
            if (confirm('Are you sure you want to cancel this subscription? This action cannot be undone.')) {
                // Implement cancel functionality
                console.log('Cancelling subscription:', id);
            }
        }
    </script>
@endsection