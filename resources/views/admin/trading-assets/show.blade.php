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
                        <a href="{{ route('admin.trading-assets.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back to Assets
                        </a>
                        <a href="{{ route('admin.trading-assets.edit', $asset->id) }}" class="btn btn-warning">
                            <i class="fa fa-edit"></i> Edit Asset
                        </a>
                        <form method="POST" action="{{ route('admin.trading-assets.destroy', $asset->id) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to delete this asset?')">
                                <i class="fa fa-trash"></i> Delete Asset
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mb-5 row">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    @if($asset->icon_url)
                                        <img src="{{ $asset->icon_url }}" alt="{{ $asset->symbol }}" 
                                             style="width: 40px; height: 40px; margin-right: 12px;">
                                    @endif
                                    <div>
                                        <h4 class="card-title mb-0">{{ $asset->name }}</h4>
                                        <small class="text-muted">{{ $asset->symbol }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <label class="font-weight-bold">Asset Type:</label>
                                            <span class="badge badge-info ml-2">{{ ucfirst($asset->type) }}</span>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <label class="font-weight-bold">Current Price:</label>
                                            <span class="ml-2">${{ $asset->formatted_price }}</span>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <label class="font-weight-bold">24h Change:</label>
                                            <span class="badge {{ $asset->change_24h >= 0 ? 'badge-success' : 'badge-danger' }} ml-2">
                                                {{ $asset->change_24h >= 0 ? '+' : '' }}{{ $asset->change_24h }}%
                                            </span>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <label class="font-weight-bold">Status:</label>
                                            <span class="badge {{ $asset->is_active ? 'badge-success' : 'badge-secondary' }} ml-2">
                                                {{ $asset->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        @if($asset->market_cap)
                                            <div class="info-item mb-3">
                                                <label class="font-weight-bold">Market Cap:</label>
                                                <span class="ml-2">${{ number_format($asset->market_cap, 0) }}</span>
                                            </div>
                                        @endif
                                        
                                        <div class="info-item mb-3">
                                            <label class="font-weight-bold">Associated Bots:</label>
                                            <span class="badge badge-primary ml-2">{{ $asset->tradingBots->count() }} bots</span>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <label class="font-weight-bold">Created:</label>
                                            <span class="ml-2">{{ $asset->created_at->format('M d, Y \a\t g:i A') }}</span>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <label class="font-weight-bold">Last Updated:</label>
                                            <span class="ml-2">{{ $asset->updated_at->format('M d, Y \a\t g:i A') }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($asset->description)
                                    <div class="info-item mt-4">
                                        <label class="font-weight-bold">Description:</label>
                                        <p class="mt-2">{{ $asset->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="card-title">Trading Bots Using This Asset</h5>
                            </div>
                            <div class="card-body">
                                @forelse($asset->tradingBots as $bot)
                                    <div class="bot-item mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $bot->name }}</h6>
                                                <small class="text-muted">
                                                    Allocation: {{ $bot->pivot->allocation_percentage }}%
                                                </small>
                                            </div>
                                            <span class="badge {{ $bot->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $bot->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                Profit Rate: {{ $bot->profit_rate }}% | 
                                                Duration: {{ $bot->duration_hours }}h
                                            </small>
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('admin.trading-bots.show', $bot->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                View Bot Details
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted text-center">
                                        No trading bots are currently using this asset.
                                    </p>
                                    <div class="text-center">
                                        <a href="{{ route('admin.trading-bots.index') }}" class="btn btn-sm btn-primary">
                                            Manage Trading Bots
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        
                        @if($asset->is_active)
                            <div class="card shadow mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">Price Simulation</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="price-display mb-3">
                                        <h3 class="text-primary">${{ $asset->formatted_price }}</h3>
                                        <small class="text-muted">Current Price</small>
                                    </div>
                                    <button class="btn btn-sm btn-info" onclick="simulatePrice()">
                                        <i class="fa fa-refresh"></i> Simulate Price Change
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        function simulatePrice() {
            // Simple price simulation for demonstration
            const currentPrice = {{ $asset->current_price }};
            const changePercent = (Math.random() - 0.5) * 10; // -5% to +5% change
            const newPrice = currentPrice * (1 + changePercent / 100);
            
            $('.price-display h3').text('$' + newPrice.toFixed(8));
            $('.price-display small').text('Simulated Price');
            
            // Show change indicator
            const changeColor = changePercent >= 0 ? 'text-success' : 'text-danger';
            const changeSymbol = changePercent >= 0 ? '+' : '';
            $('.price-display').append(
                `<div class="${changeColor}"><small>${changeSymbol}${changePercent.toFixed(2)}% change</small></div>`
            );
        }
    </script>
@endsection
