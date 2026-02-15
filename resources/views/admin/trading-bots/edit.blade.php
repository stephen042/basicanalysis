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

                <div class="mb-5 row">
                    <div class="col-md-8 card p-4 shadow">
                        <form action="{{ route('admin.trading-bots.update', $tradingBot->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="form-group">
                                <label for="name">Bot Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $tradingBot->name) }}" required>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description', $tradingBot->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="min_amount">Minimum Amount ($)</label>
                                        <input type="number" name="min_amount" id="min_amount" class="form-control" step="0.01" min="1" value="{{ old('min_amount', $tradingBot->min_amount) }}" required>
                                        @error('min_amount')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_amount">Maximum Amount ($)</label>
                                        <input type="number" name="max_amount" id="max_amount" class="form-control" step="0.01" min="1" value="{{ old('max_amount', $tradingBot->max_amount) }}" required>
                                        @error('max_amount')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="duration">Duration (Hours)</label>
                                        <input type="number" name="duration" id="duration" class="form-control" min="1" value="{{ old('duration', $tradingBot->duration) }}" required>
                                        @error('duration')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="profit_rate">Profit Rate (%)</label>
                                        <input type="number" name="profit_rate" id="profit_rate" class="form-control" step="0.01" min="0.01" max="100" value="{{ old('profit_rate', $tradingBot->profit_rate) }}" required>
                                        @error('profit_rate')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="active" {{ old('status', $tradingBot->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $tradingBot->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Update Trading Bot
                                </button>
                                <a href="{{ route('admin.trading-bots.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3">
                            <h5>Bot Statistics</h5>
                            @php
                                $totalSubscribers = \App\Models\UserTradingBot::where('trading_bot_id', $tradingBot->id)->count();
                                $activeSubscribers = \App\Models\UserTradingBot::where('trading_bot_id', $tradingBot->id)->where('status', 'active')->count();
                                $totalInvested = \App\Models\UserTradingBot::where('trading_bot_id', $tradingBot->id)->sum('amount');
                            @endphp
                            <ul class="list-unstyled">
                                <li><strong>Total Subscribers:</strong> {{ $totalSubscribers }}</li>
                                <li><strong>Active Subscriptions:</strong> {{ $activeSubscribers }}</li>
                                <li><strong>Total Invested:</strong> ${{ number_format($totalInvested) }}</li>
                                <li><strong>Created:</strong> {{ $tradingBot->created_at->format('M d, Y') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
