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
                        <a href="{{ route('admin.system-logs.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back to Logs
                        </a>
                        <a href="{{ route('admin.system-logs.download', $filename) }}" class="btn btn-success">
                            <i class="fa fa-download"></i> Download Log
                        </a>
                    </div>
                </div>

                <div class="mb-5 row">
                    <div class="col card p-3 shadow">
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> 
                            <strong>Viewing:</strong> storage/logs/{{ $filename }}
                        </div>

                        <div class="bs-example widget-shadow table-responsive" data-example-id="hoverable-table">
                            <span style="margin:3px;">
                                <table id="ShipTable" class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width: 180px;">Timestamp</th>
                                            <th style="width: 100px;">Level</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($logEntries as $index => $entry)
                                            <tr>
                                                <td>
                                                    <small>{{ $entry['timestamp'] }}</small>
                                                </td>
                                                <td>
                                                    @if($entry['level'] == 'ERROR' || $entry['level'] == 'CRITICAL' || $entry['level'] == 'ALERT' || $entry['level'] == 'EMERGENCY')
                                                        <span class="badge badge-danger">{{ $entry['level'] }}</span>
                                                    @elseif($entry['level'] == 'WARNING' || $entry['level'] == 'NOTICE')
                                                        <span class="badge badge-warning">{{ $entry['level'] }}</span>
                                                    @elseif($entry['level'] == 'INFO')
                                                        <span class="badge badge-info">{{ $entry['level'] }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ $entry['level'] }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="log-message">
                                                        <strong>{{ Str::limit($entry['message'], 150) }}</strong>
                                                        @if(!empty($entry['stack']))
                                                            <button class="btn btn-sm btn-link" 
                                                                    type="button" 
                                                                    data-toggle="collapse" 
                                                                    data-target="#stack-{{ $index }}" 
                                                                    aria-expanded="false">
                                                                <i class="fa fa-code"></i> View Stack Trace
                                                            </button>
                                                            <div class="collapse mt-2" id="stack-{{ $index }}">
                                                                <div class="card card-body bg-dark text-light" style="max-height: 300px; overflow-y: auto;">
                                                                    <pre class="mb-0"><code>{{ $entry['stack'] }}</code></pre>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">
                                                    <div class="alert alert-info">
                                                        <i class="fa fa-info-circle"></i> No log entries found in this file.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .log-message pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 11px;
        }
        .log-message code {
            color: #00ff00;
        }
    </style>
@endsection
