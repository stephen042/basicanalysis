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
                        <a href="{{ route('admin.trading-assets.show', $asset->id) }}" class="btn btn-info">
                            <i class="fa fa-eye"></i> View Asset
                        </a>
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
                                        <h4 class="card-title mb-0">Edit {{ $asset->name }}</h4>
                                        <small class="text-muted">{{ $asset->symbol }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.trading-assets.update', $asset->id) }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="symbol">Asset Symbol *</label>
                                                <input type="text" class="form-control" id="symbol" name="symbol" 
                                                       value="{{ old('symbol', $asset->symbol) }}" placeholder="e.g., BTC, AAPL, EUR/USD" required>
                                                @error('symbol')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Asset Name *</label>
                                                <input type="text" class="form-control" id="name" name="name" 
                                                       value="{{ old('name', $asset->name) }}" placeholder="e.g., Bitcoin, Apple Inc." required>
                                                @error('name')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type">Asset Type *</label>
                                                <select class="form-control" id="type" name="type" required>
                                                    <option value="">Select Type</option>
                                                    <option value="crypto" {{ old('type', $asset->type) == 'crypto' ? 'selected' : '' }}>Cryptocurrency</option>
                                                    <option value="stock" {{ old('type', $asset->type) == 'stock' ? 'selected' : '' }}>Stock</option>
                                                    <option value="forex" {{ old('type', $asset->type) == 'forex' ? 'selected' : '' }}>Forex</option>
                                                    <option value="commodity" {{ old('type', $asset->type) == 'commodity' ? 'selected' : '' }}>Commodity</option>
                                                </select>
                                                @error('type')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="current_price">Current Price ($) *</label>
                                                <input type="number" class="form-control" id="current_price" name="current_price" 
                                                       value="{{ old('current_price', $asset->current_price) }}" step="0.00000001" min="0" placeholder="0.00" required>
                                                @error('current_price')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="change_24h">24h Change (%)</label>
                                                <input type="number" class="form-control" id="change_24h" name="change_24h" 
                                                       value="{{ old('change_24h', $asset->change_24h) }}" step="0.01" placeholder="0.00">
                                                <small class="text-muted">Use negative values for decreases</small>
                                                @error('change_24h')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="market_cap">Market Cap ($)</label>
                                                <input type="number" class="form-control" id="market_cap" name="market_cap" 
                                                       value="{{ old('market_cap', $asset->market_cap) }}" step="1" min="0" placeholder="0">
                                                @error('market_cap')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="icon_url">Icon URL</label>
                                        <input type="url" class="form-control" id="icon_url" name="icon_url" 
                                               value="{{ old('icon_url', $asset->icon_url) }}" placeholder="https://example.com/icon.png">
                                        <small class="text-muted">URL to the asset's icon/logo</small>
                                        @error('icon_url')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  placeholder="Brief description of the asset">{{ old('description', $asset->description) }}</textarea>
                                        @error('description')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $asset->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active (available for trading)
                                        </label>
                                    </div>

                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Update Trading Asset
                                        </button>
                                        <a href="{{ route('admin.trading-assets.show', $asset->id) }}" class="btn btn-secondary ml-2">
                                            Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="card-title">Current Asset Info</h5>
                            </div>
                            <div class="card-body">
                                <div class="info-item mb-3">
                                    <label class="font-weight-bold">Current Status:</label>
                                    <span class="badge {{ $asset->is_active ? 'badge-success' : 'badge-secondary' }} ml-2">
                                        {{ $asset->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <label class="font-weight-bold">Associated Bots:</label>
                                    <span class="badge badge-primary ml-2">{{ $asset->tradingBots->count() }} bots</span>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <label class="font-weight-bold">Created:</label>
                                    <small class="ml-2">{{ $asset->created_at->format('M d, Y') }}</small>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <label class="font-weight-bold">Last Updated:</label>
                                    <small class="ml-2">{{ $asset->updated_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                        
                        @if($asset->tradingBots->count() > 0)
                            <div class="card shadow mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">Warning</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <strong>Caution:</strong> This asset is currently being used by {{ $asset->tradingBots->count() }} trading bot(s). 
                                        Changes to price or status may affect active trading simulations.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
