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

                <div class="row">
                    <!-- Agent Information -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Agent Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $agentUser->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $agentUser->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>{{ $agentUser->phone ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @if($agentUser->status == 'active')
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Joined:</strong></td>
                                            <td>{{ $agentUser->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agent Statistics -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Agent Statistics</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-primary text-white mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title">Total Referrals</h5>
                                                <h2>{{ $stats['total_referrals'] }}</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-success text-white mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title">Active Referrals</h5>
                                                <h2>{{ $stats['active_referrals'] }}</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-info text-white mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title">Total Deposits</h5>
                                                <h2>${{ number_format($stats['total_deposits'], 2) }}</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-warning text-white mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title">Commission Earned</h5>
                                                <h2>${{ number_format($stats['commission_earned'], 2) }}</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Actions</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('agents') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left"></i> Back to Agents
                                </a>
                                @if($agentUser->status == 'active')
                                    <a href="{{ route('ublock', $agentUser->id) }}" class="btn btn-warning">
                                        <i class="fa fa-ban"></i> Block Agent
                                    </a>
                                @else
                                    <a href="{{ route('unblock', $agentUser->id) }}" class="btn btn-success">
                                        <i class="fa fa-check"></i> Activate Agent
                                    </a>
                                @endif
                                <a href="{{ route('delagent', $agentUser->id) }}" class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this agent?')">
                                    <i class="fa fa-trash"></i> Delete Agent
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
