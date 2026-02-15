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
                    <p class="text-muted">Edit expert trader information and settings</p>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.copy-trading.index') }}">Copy Trading</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.copy-trading.experts') }}">Expert Traders</a></li>
                        <li class="breadcrumb-item active">Edit {{ $expert->name }}</li>
                    </ol>
                </nav>

                <!-- Edit Expert Form -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Expert Trader Information</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.copy-trading.update-expert', $expert->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <!-- Current Avatar Display -->
                                        @if($expert->avatar)
                                            <div class="col-12 mb-3">
                                                <label>Current Avatar</label><br>
                                                <img src="{{ $expert->avatar }}" alt="{{ $expert->name }}" class="rounded-circle" width="80" height="80">
                                            </div>
                                        @endif

                                        <!-- Basic Information -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Expert Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                       id="name" name="name" value="{{ old('name', $expert->name) }}" required>
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
                                                    <option value="Cryptocurrency" {{ old('specialization', $expert->specialization) == 'Cryptocurrency' ? 'selected' : '' }}>Cryptocurrency</option>
                                                    <option value="Forex" {{ old('specialization', $expert->specialization) == 'Forex' ? 'selected' : '' }}>Forex</option>
                                                    <option value="Stocks" {{ old('specialization', $expert->specialization) == 'Stocks' ? 'selected' : '' }}>Stocks</option>
                                                    <option value="Commodities" {{ old('specialization', $expert->specialization) == 'Commodities' ? 'selected' : '' }}>Commodities</option>
                                                    <option value="Multi-Asset" {{ old('specialization', $expert->specialization) == 'Multi-Asset' ? 'selected' : '' }}>Multi-Asset</option>
                                                </select>
                                                @error('specialization')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="experience_years">Experience (Years) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('experience_years') is-invalid @enderror" 
                                                       id="experience_years" name="experience_years" value="{{ old('experience_years', $expert->experience_years) }}" 
                                                       min="1" max="50" required>
                                                @error('experience_years')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="status">Status <span class="text-danger">*</span></label>
                                                <select class="form-control @error('status') is-invalid @enderror" 
                                                        id="status" name="status" required>
                                                    <option value="active" {{ old('status', $expert->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('status', $expert->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                    <option value="suspended" {{ old('status', $expert->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                </select>
                                                @error('status')
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
                                                       id="roi_percentage" name="roi_percentage" value="{{ old('roi_percentage', $expert->roi_percentage) }}" 
                                                       step="0.01" required>
                                                @error('roi_percentage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="win_rate">Win Rate (%) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('win_rate') is-invalid @enderror" 
                                                       id="win_rate" name="win_rate" value="{{ old('win_rate', $expert->win_rate) }}" 
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
                                                       id="portfolio_value" name="portfolio_value" value="{{ old('portfolio_value', $expert->portfolio_value) }}" 
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
                                                       id="total_followers" name="total_followers" value="{{ old('total_followers', $expert->total_followers) }}" 
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
                                                       id="total_pnl" name="total_pnl" value="{{ old('total_pnl', $expert->total_pnl) }}" 
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
                                                       id="min_copy_amount" name="min_copy_amount" value="{{ old('min_copy_amount', $expert->min_copy_amount) }}" 
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
                                                       id="max_copy_amount" name="max_copy_amount" value="{{ old('max_copy_amount', $expert->max_copy_amount) }}" 
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
                                                       id="avatar" name="avatar" value="{{ old('avatar', $expert->avatar) }}" 
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
                                                          id="description" name="description" rows="3">{{ old('description', $expert->description) }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="trading_strategy">Trading Strategy</label>
                                                <textarea class="form-control @error('trading_strategy') is-invalid @enderror" 
                                                          id="trading_strategy" name="trading_strategy" rows="4">{{ old('trading_strategy', $expert->trading_strategy) }}</textarea>
                                                @error('trading_strategy')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Current Statistics Display -->
                                    <div class="row">
                                        <div class="col-12">
                                            <hr>
                                            <h6 class="text-primary">Current Performance Statistics</h6>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-info text-white">
                                                <div class="card-body text-center">
                                                    <h5>{{ $expert->getActiveSubscribersCount() }}</h5>
                                                    <small>Active Subscribers</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-success text-white">
                                                <div class="card-body text-center">
                                                    <h5>${{ number_format($expert->getTotalCopiedAmount()) }}</h5>
                                                    <small>Total Copied Amount</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-warning text-white">
                                                <div class="card-body text-center">
                                                    <h5>{{ $expert->expertTrades()->count() }}</h5>
                                                    <small>Total Trades</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body text-center">
                                                    <h5>${{ number_format($expert->total_pnl, 2) }}</h5>
                                                    <small>Total P&L</small>
                                                </div>
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
                                                    <i class="fas fa-save mr-2"></i>Update Expert Trader
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
@endsection