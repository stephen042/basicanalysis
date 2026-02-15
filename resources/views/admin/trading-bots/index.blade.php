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
                        <a href="{{ route('admin.trading-bots.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New Trading Bot
                        </a>
                        <a href="{{ route('admin.trading-bots.subscribers') }}" class="btn btn-info">
                            <i class="fa fa-users"></i> View Subscribers
                        </a>
                        <a href="{{ route('admin.trading-bots.logs') }}" class="btn btn-warning">
                            <i class="fa fa-chart-line"></i> Trading Logs
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
                                            <th>Bot Name</th>
                                            <th>Min Amount</th>
                                            <th>Max Amount</th>
                                            <th>Duration (Hours)</th>
                                            <th>Profit Rate (%)</th>
                                            <th>Status</th>
                                            <th>Subscribers</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tradingBots as $bot)
                                            <tr>
                                                <th scope="row">{{ $bot->id }}</th>
                                                <td>{{ $bot->name }}</td>
                                                <td>${{ number_format($bot->min_amount) }}</td>
                                                <td>${{ number_format($bot->max_amount) }}</td>
                                                <td>{{ $bot->duration }}</td>
                                                <td>{{ $bot->profit_rate }}%</td>
                                                <td>
                                                    @if ($bot->status == 'active')
                                                        <span class="badge badge-success">{{ ucfirst($bot->status) }}</span>
                                                    @else
                                                        <span class="badge badge-danger">{{ ucfirst($bot->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $subscriberCount = \App\Models\UserTradingBot::where('trading_bot_id', $bot->id)->count();
                                                    @endphp
                                                    <span class="badge badge-info">{{ $subscriberCount }}</span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($bot->created_at)->toDayDateTimeString() }}</td>
                                                <td>
                                                    <a href="{{ route('admin.trading-bots.edit', $bot->id) }}"
                                                        class="m-1 btn btn-info btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                                    <form action="{{ route('admin.trading-bots.destroy', $bot->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this trading bot?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="m-1 btn btn-danger btn-sm">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
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
