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
                    <h1 class="title1 ">System Error Logs</h1>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <div class="mb-3 row">
                    <div class="col">
                        @if(count($logs) > 0)
                            <form action="{{ route('admin.system-logs.clear') }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete ALL log files? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> Clear All Logs
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="mb-5 row">
                    <div class="col card p-3 shadow ">
                        <div class="bs-example widget-shadow table-responsive" data-example-id="hoverable-table">
                            <span style="margin:3px;">
                                <table id="ShipTable" class="table table-hover ">
                                    <thead>
                                        <tr>
                                            <th>Log File Name</th>
                                            <th>File Size</th>
                                            <th>Last Modified</th>
                                            <th>Option</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($logs as $log)
                                            <tr>
                                                <td>
                                                    <i class="fa fa-file-alt text-danger"></i> 
                                                    <strong>{{ $log['name'] }}</strong>
                                                </td>
                                                <td>{{ $log['size'] }}</td>
                                                <td>{{ \Carbon\Carbon::parse($log['modified'])->toDayDateTimeString() }}</td>
                                                <td>
                                                    <a href="{{ route('admin.system-logs.view', basename($log['name'])) }}" 
                                                       class="m-1 btn btn-info btn-sm">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                    <a href="{{ route('admin.system-logs.download', basename($log['name'])) }}" 
                                                       class="m-1 btn btn-success btn-sm">
                                                        <i class="fa fa-download"></i> Download
                                                    </a>
                                                    <form action="{{ route('admin.system-logs.delete', basename($log['name'])) }}" 
                                                          method="POST" 
                                                          style="display: inline-block;"
                                                          onsubmit="return confirm('Are you sure you want to delete this log file?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="m-1 btn btn-danger btn-sm">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    <div class="alert alert-info">
                                                        <i class="fa fa-info-circle"></i> No log files found in storage/logs directory.
                                                    </div>
                                                </td>
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
@endsection
