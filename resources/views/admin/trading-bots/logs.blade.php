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
                        <a href="{{ route('admin.trading-bots.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back to Trading Bots
                        </a>
                        <a href="{{ route('admin.trading-bots.subscribers') }}" class="btn btn-info">
                            <i class="fa fa-users"></i> View Subscribers
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
                                            <th>Client name</th>
                                            <th>Trading Bot</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Investment Amount</th>
                                            <th>Client Email</th>
                                            <th>Date created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($logs as $log)
                                            <tr>
                                                <th scope="row">{{ $log->id }}</th>
                                                <td>{{ $log->userTradingBot->user->name }}</td>
                                                <td>{{ $log->userTradingBot->tradingBot->name }}</td>
                                                <td>
                                                    @if ($log->type == 'profit')
                                                        <span class="badge badge-success">{{ ucfirst($log->type) }}</span>
                                                    @else
                                                        <span class="badge badge-danger">{{ ucfirst($log->type) }}</span>
                                                    @endif
                                                </td>
                                                <td>${{ number_format($log->amount, 2) }}</td>
                                                <td>${{ number_format($log->userTradingBot->amount) }}</td>
                                                <td>{{ $log->userTradingBot->user->email }}</td>
                                                <td>{{ \Carbon\Carbon::parse($log->created_at)->toDayDateTimeString() }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
