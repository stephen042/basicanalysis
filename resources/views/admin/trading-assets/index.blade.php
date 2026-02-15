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
                        <a href="{{ route('admin.trading-assets.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New Trading Asset
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
                                            <th>Symbol</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Current Price</th>
                                            <th>24h Change</th>
                                            <th>Status</th>
                                            <th>Associated Bots</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($assets as $asset)
                                            <tr>
                                                <td>{{ $asset->id }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($asset->icon_url)
                                                            <img src="{{ $asset->icon_url }}" alt="{{ $asset->symbol }}" 
                                                                 style="width: 24px; height: 24px; margin-right: 8px;">
                                                        @endif
                                                        <strong>{{ $asset->symbol }}</strong>
                                                    </div>
                                                </td>
                                                <td>{{ $asset->name }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ ucfirst($asset->type) }}
                                                    </span>
                                                </td>
                                                <td>${{ $asset->formatted_price }}</td>
                                                <td>
                                                    <span class="badge {{ $asset->change_24h >= 0 ? 'badge-success' : 'badge-danger' }}">
                                                        {{ $asset->change_24h >= 0 ? '+' : '' }}{{ $asset->change_24h }}%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $asset->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                        {{ $asset->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        {{ $asset->trading_bots_count ?? 0 }} bots
                                                    </span>
                                                </td>
                                                <td>{{ $asset->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.trading-assets.show', $asset->id) }}" 
                                                           class="btn btn-info btn-sm">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.trading-assets.edit', $asset->id) }}" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <form method="POST" 
                                                              action="{{ route('admin.trading-assets.destroy', $asset->id) }}" 
                                                              style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                                    onclick="return confirm('Are you sure you want to delete this asset?')">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center">
                                                    <p class="text-muted">No trading assets found.</p>
                                                    <a href="{{ route('admin.trading-assets.create') }}" class="btn btn-primary">
                                                        <i class="fa fa-plus"></i> Add First Trading Asset
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </span>
                        </div>
            
                        @include('admin.components.pagination', ['paginator' => $assets])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        // Add any custom JavaScript for the trading assets page here
        console.log('Trading Assets page loaded');
    </script>
@endsection
