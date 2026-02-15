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
    <div class="main-panel ">
        <div class="content ">
            <div class="page-inner">
                <div class="mt-2 mb-5">
                    <h1 class="title1 d-inline text-{{ $text }}">View Deposit Screenshot</h1>
                    <div class="d-inline">
                        <div class="float-right btn-group">
                            <a class="btn btn-primary btn-sm" href="{{ route('mdeposits') }}"> <i class="fa fa-arrow-left"></i>
                                back</a>
                        </div>
                    </div>
                </div>
                <x-danger-alert />
                <x-success-alert />
                <div class="mb-5 row">
                    <div class="col-lg-8 offset-lg-2 card p-4  shadow">
                        @if($deposit->duser)
                            <div class="mb-3">
                                <h5 class="text-{{ $text }}">Deposit Details</h5>
                                <p><strong>Client Name:</strong> {{ $deposit->duser->name }}</p>
                                <p><strong>Client Email:</strong> {{ $deposit->duser->email }}</p>
                                <p><strong>Amount:</strong> {{ $deposit->duser->currency }}{{ number_format($deposit->amount, 2) }}</p>
                                <p><strong>Payment Method:</strong> {{ $deposit->payment_mode }}</p>
                                <p><strong>Status:</strong> 
                                    @if ($deposit->status == 'Processed')
                                        <span class="badge badge-success">{{ $deposit->status }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ $deposit->status }}</span>
                                    @endif
                                </p>
                                <p><strong>Date:</strong> {{ $deposit->created_at->toDayDateTimeString() }}</p>
                            </div>
                            <hr>
                            <h5 class="text-{{ $text }} mb-3">Payment Screenshot</h5>
                            <img src="{{ asset('storage/app/public/' . $deposit->proof) }}" alt="Proof of Payment"
                                class="img-fluid" />
                        @else
                            <div class="alert alert-danger">
                                <h4><i class="fa fa-exclamation-triangle"></i> User Account Deleted</h4>
                                <p>The user associated with this deposit has been deleted from the system.</p>
                                <hr>
                                <div class="mb-3">
                                    <strong>Deposit ID:</strong> {{ $deposit->id }}<br>
                                    <strong>Amount:</strong> {{ $settings->currency }}{{ number_format($deposit->amount, 2) }}<br>
                                    <strong>Payment Method:</strong> {{ $deposit->payment_mode }}<br>
                                    <strong>Status:</strong> <span class="badge badge-{{ $deposit->status == 'Processed' ? 'success' : 'danger' }}">{{ $deposit->status }}</span><br>
                                    <strong>Date:</strong> {{ $deposit->created_at->toDayDateTimeString() }}
                                </div>
                                @if($deposit->proof)
                                    <hr>
                                    <h5>Payment Screenshot</h5>
                                    <img src="{{ asset('storage/app/public/' . $deposit->proof) }}" alt="Proof of Payment"
                                        class="img-fluid" />
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection
