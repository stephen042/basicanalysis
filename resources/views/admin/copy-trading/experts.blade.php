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
                    <p class="text-muted">Create, edit and manage expert traders for copy trading</p>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <!-- Actions Bar -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="{{ route('admin.copy-trading.create-expert') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>Create New Expert Trader
                        </a>
                        <a href="{{ route('admin.copy-trading.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="input-group" style="max-width: 300px; margin-left: auto;">
                            <input type="text" class="form-control" placeholder="Search experts..." id="searchInput">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expert Traders Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Expert Traders ({{ $experts->total() }})</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Expert</th>
                                                <th>Specialization</th>
                                                <th>Performance</th>
                                                <th>Subscribers</th>
                                                <th>Fees</th>
                                                <th>Status</th>
                                                <th>Last Active</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($experts as $expert)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3">
                                                                @if($expert->avatar)
                                                                    <img src="{{ asset($expert->avatar) }}" alt="{{ $expert->name }}" class="rounded-circle" width="40" height="40">
                                                                @else
                                                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                                                        {{ substr($expert->name, 0, 1) }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $expert->name }}</h6>
                                                                <small class="text-muted">{{ $expert->experience_years }}y experience</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $expert->specialization }}</strong><br>
                                                        <small class="text-muted">Risk: {{ $expert->risk_score }}/10</small>
                                                    </td>
                                                    <td>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <small class="text-success font-weight-bold">
                                                                    {{ number_format($expert->current_roi, 1) }}% ROI
                                                                </small>
                                                            </div>
                                                            <div class="col-12">
                                                                <small class="text-info">
                                                                    {{ number_format($expert->current_win_rate, 1) }}% Win Rate
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $expert->active_subscribers }}</strong><br>
                                                        <small class="text-muted">{{ $expert->total_followers }} total</small>
                                                    </td>
                                                    <td>
                                                        @if($expert->subscription_fee > 0)
                                                            <small>Sub: ${{ number_format($expert->subscription_fee) }}/mo</small><br>
                                                        @endif
                                                        @if($expert->performance_fee > 0)
                                                            <small>Perf: {{ $expert->performance_fee }}%</small>
                                                        @endif
                                                        @if($expert->subscription_fee == 0 && $expert->performance_fee == 0)
                                                            <span class="badge badge-success">Free</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $expert->status === 'active' ? 'success' : ($expert->status === 'inactive' ? 'secondary' : 'danger') }}">
                                                            {{ ucfirst($expert->status) }}
                                                        </span>
                                                        @if($expert->isOnline())
                                                            <br><small class="text-success">● Online</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($expert->last_active_at)
                                                            <small>{{ $expert->last_active_at->diffForHumans() }}</small>
                                                        @else
                                                            <small class="text-muted">Never</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('admin.copy-trading.edit-expert', $expert->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            @if($expert->active_subscribers == 0)
                                                                <form action="{{ route('admin.copy-trading.delete-expert', $expert->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expert trader?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete expert with active subscribers">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="fas fa-users fa-3x mb-3"></i>
                                                            <h5>No Expert Traders Found</h5>
                                                            <p>Create your first expert trader to get started with copy trading.</p>
                                                            <a href="{{ route('admin.copy-trading.create-expert') }}" class="btn btn-primary">
                                                                <i class="fas fa-plus mr-2"></i>Create Expert Trader
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if($experts->hasPages())
                                <div class="card-footer">
                                    {{ $experts->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple search functionality
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
    </script>
@endsection