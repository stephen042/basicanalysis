<?php
if (Auth('admin')->User()->dashboard_style == 'light') {
    $text = 'dark';
    $bg = 'light';
} else {
    $text = 'light';
    $bg = 'dark';
}
?>
@extends('layouts.app')
@section('content')
    @include('admin.topmenu')
    @include('admin.sidebar')
    <div class="main-panel">
        <div class="content">
            <div class="page-inner">
                <div class="row">
                    {{-- Form Column --}}
                    <div class="col-md-4">
                        <div class="card" style="background: #1a202c; border-radius: 12px; color: white;">
                            <div class="card-header">
                                <h4 class="card-title">Create Signal Plan</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('storesignal') }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label>Plan Name</label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="e.g. Open Sea Plan" required
                                            style="background: #2d3748; color: white; border: 1px solid #4a5568;">
                                    </div>
                                    <div class="form-group">
                                        <label>Duration (Days)</label>
                                        <input type="number" name="duration" class="form-control" placeholder="30" required
                                            style="background: #2d3748; color: white; border: 1px solid #4a5568;">
                                    </div>
                                    <div class="form-group">
                                        <label>Amount (Price)</label>
                                        <input type="number" step="0.01" name="amount" class="form-control"
                                            placeholder="100.00" required
                                            style="background: #2d3748; color: white; border: 1px solid #4a5568;">
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control"
                                            style="background: #2d3748; color: white; border: 1px solid #4a5568;">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block mt-3"
                                        style="font-weight: bold;">Create Plan</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Table Column --}}
                    <div class="col-md-8">
                        <div class="card" style="background: #1a202c; border-radius: 12px; color: white;">
                            <div class="card-header">
                                <h4 class="card-title">Existing Signal Plans</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="max-height: 500px; overflow-x: auto;">
                                    <table class="table" style="color: white; min-width: 500px;">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Duration</th>
                                                <th>Price</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($signals as $sig)
                                                <tr style="border-bottom: 1px solid #2d3748;">
                                                    <td>{{ $sig->name }}</td>
                                                    <td>{{ $sig->duration }} Days</td>
                                                    <td>{{ $settings->currency }}{{ number_format($sig->amount, 2) }}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $sig->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                                                            {{ $sig->status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <form action="{{ route('deletesignal', $sig->id) }}" method="POST"
                                                            onsubmit="return confirm('Delete this plan?')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No plans created yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
