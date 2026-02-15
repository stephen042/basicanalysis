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
                    <p class="text-muted">Create a new expert trader for copy trading system</p>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.copy-trading.index') }}">Copy Trading</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.copy-trading.experts') }}">Expert Traders</a></li>
                        <li class="breadcrumb-item active">Create New</li>
                    </ol>
                </nav>

                <!-- Create Expert Form -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Expert Trader Information</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.copy-trading.store-expert') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="row">
                                        <!-- Basic Information -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Expert Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                       id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="specialization">Specialization <span class="text-danger">*</span></label>
                                                <select class="form-control @error('specialization') is-invalid @enderror" 
                                                        id="specialization" name="specialization" required>
                                                    <option value="">Select Specialization</option>
                                                    <option value="Cryptocurrency" {{ old('specialization') == 'Cryptocurrency' ? 'selected' : '' }}>Cryptocurrency</option>
                                                    <option value="Forex" {{ old('specialization') == 'Forex' ? 'selected' : '' }}>Forex</option>
                                                    <option value="Stocks" {{ old('specialization') == 'Stocks' ? 'selected' : '' }}>Stocks</option>
                                                    <option value="Commodities" {{ old('specialization') == 'Commodities' ? 'selected' : '' }}>Commodities</option>
                                                    <option value="Multi-Asset" {{ old('specialization') == 'Multi-Asset' ? 'selected' : '' }}>Multi-Asset</option>
                                                </select>
                                                @error('specialization')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="experience_years">Experience (Years) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('experience_years') is-invalid @enderror" 
                                                       id="experience_years" name="experience_years" value="{{ old('experience_years') }}" 
                                                       min="1" max="50" required>
                                                @error('experience_years')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Performance Metrics -->
                                        <div class="col-12">
                                            <hr>
                                            <h6 class="text-primary">Performance Metrics</h6>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="roi_percentage">ROI Percentage <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('roi_percentage') is-invalid @enderror" 
                                                       id="roi_percentage" name="roi_percentage" value="{{ old('roi_percentage') }}" 
                                                       step="0.01" required>
                                                <small class="form-text text-muted">Expected annual ROI (%)</small>
                                                @error('roi_percentage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="win_rate">Win Rate (%) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('win_rate') is-invalid @enderror" 
                                                       id="win_rate" name="win_rate" value="{{ old('win_rate') }}" 
                                                       min="0" max="100" step="0.01" required>
                                                @error('win_rate')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="portfolio_value">Portfolio Value ($) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('portfolio_value') is-invalid @enderror" 
                                                       id="portfolio_value" name="portfolio_value" value="{{ old('portfolio_value') }}" 
                                                       min="1000" step="0.01" required>
                                                @error('portfolio_value')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="total_followers">Total Followers <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('total_followers') is-invalid @enderror" 
                                                       id="total_followers" name="total_followers" value="{{ old('total_followers', 0) }}" 
                                                       min="0" step="1" required>
                                                <small class="form-text text-muted">Number of users following this expert</small>
                                                @error('total_followers')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="total_pnl">Total P&L ($) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('total_pnl') is-invalid @enderror" 
                                                       id="total_pnl" name="total_pnl" value="{{ old('total_pnl', 0) }}" 
                                                       step="0.01" required>
                                                <small class="form-text text-muted">Total profit/loss (use negative for losses)</small>
                                                @error('total_pnl')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Fee Structure -->
                                        <div class="col-12">
                                            <hr>
                                            <h6 class="text-primary">Fee Structure</h6>
                                        </div>

                                        <!-- Copy Limits -->
                                        <div class="col-12">
                                            <hr>
                                            <h6 class="text-primary">Copy Trading Limits</h6>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="min_copy_amount">Minimum Copy Amount ($) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('min_copy_amount') is-invalid @enderror" 
                                                       id="min_copy_amount" name="min_copy_amount" value="{{ old('min_copy_amount', 100) }}" 
                                                       min="1" step="0.01" required>
                                                @error('min_copy_amount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="max_copy_amount">Maximum Copy Amount ($) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('max_copy_amount') is-invalid @enderror" 
                                                       id="max_copy_amount" name="max_copy_amount" value="{{ old('max_copy_amount', 10000) }}" 
                                                       min="1000" step="0.01" required>
                                                @error('max_copy_amount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Additional Information -->
                                        <div class="col-12">
                                            <hr>
                                            <h6 class="text-primary">Additional Information</h6>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="avatar">Profile Avatar URL</label>
                                                <input type="url" class="form-control @error('avatar') is-invalid @enderror" 
                                                       id="avatar" name="avatar" value="{{ old('avatar') }}" 
                                                       placeholder="https://example.com/avatar.jpg">
                                                <small class="form-text text-muted">Enter the URL of the expert's profile picture</small>
                                                @error('avatar')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="description">Description</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                                <small class="form-text text-muted">Brief description about the expert trader</small>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="trading_strategy">Trading Strategy</label>
                                                <textarea class="form-control @error('trading_strategy') is-invalid @enderror" 
                                                          id="trading_strategy" name="trading_strategy" rows="4">{{ old('trading_strategy') }}</textarea>
                                                <small class="form-text text-muted">Detailed explanation of trading methodology</small>
                                                @error('trading_strategy')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="row">
                                        <div class="col-12">
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ route('admin.copy-trading.experts') }}" class="btn btn-secondary">
                                                    <i class="fas fa-arrow-left mr-2"></i>Cancel
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save mr-2"></i>Create Expert Trader
                                                </button>
                                            </div>
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

    <script>
        // Validate max_copy_amount > min_copy_amount
        document.getElementById('min_copy_amount').addEventListener('change', validateCopyAmounts);
        document.getElementById('max_copy_amount').addEventListener('change', validateCopyAmounts);

        function validateCopyAmounts() {
            const minAmount = parseFloat(document.getElementById('min_copy_amount').value) || 0;
            const maxAmount = parseFloat(document.getElementById('max_copy_amount').value) || 0;
            
            if (maxAmount > 0 && minAmount > 0 && maxAmount <= minAmount) {
                document.getElementById('max_copy_amount').setCustomValidity('Maximum amount must be greater than minimum amount');
            } else {
                document.getElementById('max_copy_amount').setCustomValidity('');
            }
        }
    </script>
@endsection