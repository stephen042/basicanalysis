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
        <div class="content ">
            <div class="page-inner">
                <x-danger-alert />
                <x-success-alert />
                <!-- Beginning of  Dashboard Stats  -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="p-3 card ">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 ">
                                        <h1 class="d-inline text-primary">{{ $user->name }}</h1><span></span>
                                        <div class="d-inline">
                                            <div class="float-right btn-group">
                                                <a class="btn btn-primary btn-sm" href="{{ route('manageusers') }}"> <i
                                                        class="fa fa-arrow-left"></i> back</a> &nbsp;
                                                <button type="button" class="btn btn-secondary dropdown-toggle btn-sm"
                                                    data-toggle="dropdown" data-display="static" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-lg-right">
                                                    <a class="dropdown-item"
                                                        href="{{ route('loginactivity', $user->id) }}">Login Activity</a>
                                                    @if ($user->status == null || $user->status == 'blocked')
                                                        <a class="dropdown-item"
                                                            href="{{ url('admin/dashboard/uunblock') }}/{{ $user->id }}">Unblock</a>
                                                    @else
                                                        <a class="dropdown-item"
                                                            href="{{ url('admin/dashboard/uublock') }}/{{ $user->id }}">Block</a>
                                                    @endif
                                                    @if ($user->trade_mode == 'on')
                                                        <a class="dropdown-item"
                                                            href="{{ url('admin/dashboard/usertrademode') }}/{{ $user->id }}/off">Turn
                                                            off trade</a>
                                                    @else
                                                        <a class="dropdown-item"
                                                            href="{{ url('admin/dashboard/usertrademode') }}/{{ $user->id }}/on">Turn
                                                            on trade</a>
                                                    @endif
                                                    @if ($user->email_verified_at)
                                                    @else
                                                        <a href="{{ url('admin/dashboard/email-verify') }}/{{ $user->id }}"
                                                            class="dropdown-item">Verify Email</a>
                                                    @endif
                                                    <a href="#" data-toggle="modal" data-target="#userAction"
                                                        class="dropdown-item">Add upgrade Action</a>
                                                    {{-- <a href="#" data-toggle="modal" data-target="#userActionsignal"
                                                        class="dropdown-item">Add signal Action</a> --}}
                                                    <a href="#" data-toggle="modal" data-target="#signalStrengthModal"
                                                        class="dropdown-item">Manage Signal Strength</a>
                                                    <a href="#" data-toggle="modal"
                                                        data-target="#userNotificationModal" class="dropdown-item">Manage
                                                        User Notification</a>
                                                    <a href="#" data-toggle="modal"
                                                        data-target="#withdrawalCodesModal" class="dropdown-item">Manage
                                                        Withdrawal Codes</a>
                                                    <a href="#" data-toggle="modal" data-target="#topupModal"
                                                        class="dropdown-item">Credit/Debit</a>
                                                    <a href="#" data-toggle="modal" data-target="#manageGasFeeModal"
                                                        class="dropdown-item">Manage Gas Fee</a>
                                                    <a href="#" data-toggle="modal" data-target="#resetpswdModal"
                                                        class="dropdown-item">Reset Password</a>
                                                    <a href="#" data-toggle="modal" data-target="#clearacctModal"
                                                        class="dropdown-item">Clear Account</a>
                                                    <a href="#" data-toggle="modal" data-target="#TradingModal"
                                                        class="dropdown-item">Add Trading History</a>
                                                    <a href="#" data-toggle="modal" data-target="#edituser"
                                                        class="dropdown-item">Edit</a>
                                                    <a href="{{ route('showusers', $user->id) }}" class="dropdown-item">Add
                                                        Referral</a>
                                                    <a href="#" data-toggle="modal"
                                                        data-target="#sendmailtooneuserModal" class="dropdown-item">Send
                                                        Email</a>
                                                    <a href="#" data-toggle="modal" data-target="#switchuserModal"
                                                        class="dropdown-item text-success">Login as {{ $user->name }}</a>
                                                    <a href="#" data-toggle="modal" data-target="#deleteModal"
                                                        class="dropdown-item text-danger">Delete {{ $user->name }}</a>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 mt-4 border rounded row ">
                                    <div class="col-md-3">
                                        <h5 class="text-bold">Account Balance</h5>
                                        <p>{{ $user->currency }}{{ number_format($user->account_bal, 2) }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5>Profit</h5>
                                        <p>{{ $user->currency }}{{ number_format($user->roi, 2) }} </p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5>Referral Bonus</h5>
                                        <p>{{ $user->currency }}{{ number_format($user->ref_bonus, 2) }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5>Bonus</h5>
                                        <p>{{ $user->currency }}{{ number_format($user->bonus, 2) }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h5>User Account Status</h5>
                                        @if ($user->status == 'blocked')
                                            <span class="badge badge-danger">Blocked</span>
                                        @else
                                            <span class="badge badge-success">Active</span>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <h5>Bot Trading Plans</h5>
                                        @php
                                            $userBotsCount = \App\Models\UserTradingBot::where(
                                                'user_id',
                                                $user->id,
                                            )->count();
                                        @endphp
                                        @if ($userBotsCount > 0)
                                            <a class="btn btn-sm btn-primary d-inline"
                                                href="{{ route('admin.user.trading-bots', $user->id) }}">View Bots
                                                ({{ $userBotsCount }})</a>
                                        @else
                                            <p>No Active Bots</p>
                                        @endif

                                    </div>
                                    <div class="col-md-3">
                                        <h5>KYC</h5>
                                        @if ($user->account_verify == 'Not Verified' || $user->account_verify == null)
                                            <span class="badge badge-danger">Not Verified Yet</span>
                                        @else
                                            <span class="badge badge-success">Verified</span>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <h5>Trade Mode</h5>
                                        @if ($user->trade_mode == 'off' || $user->trade_mode == null)
                                            <span class="badge badge-danger">Off</span>
                                        @else
                                            <span class="badge badge-success">On</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3 row ">
                                    <div class="col-md-12">
                                        <h5>USER INFORMATION</h5>
                                    </div>
                                </div>
                                <div class="p-3 border row ">
                                    <div class="col-md-4 border-right">
                                        <h5>Fullname</h5>
                                    </div>
                                    <div class="col-md-8">
                                        <h5>{{ $user->name }}</h5>
                                    </div>
                                </div>
                                <div class="p-3 border row ">
                                    <div class="col-md-4 border-right">
                                        <h5>Email Address</h5>
                                    </div>
                                    <div class="col-md-8">
                                        <h5>{{ $user->email }}</h5>
                                    </div>
                                </div>
                                <div class="p-3 border row ">
                                    <div class="col-md-4 border-right">
                                        <h5>Mobile Number</h5>
                                    </div>
                                    <div class="col-md-8">
                                        <h5>{{ $user->phone }}</h5>
                                    </div>
                                </div>
                                <div class="p-3 border row ">
                                    <div class="col-md-4 border-right">
                                        <h5>Date of birth</h5>
                                    </div>
                                    <div class="col-md-8">
                                        <h5>{{ $user->dob }}</h5>
                                    </div>
                                </div>
                                <div class="p-3 border row ">
                                    <div class="col-md-4 border-right">
                                        <h5>Nationality</h5>
                                    </div>
                                    <div class="col-md-8">
                                        <h5>{{ $user->country }}</h5>
                                    </div>
                                </div>
                                {{-- <div class="p-3 border row ">
                                <div class="col-md-4 border-right">
                                    <h5>Wallet Address</h5>
                                </div>
                                <div class="col-md-8">
                                   <h5>@if ($user->wallet_address)
                                    {{$user->wallet_address}}
                                   @else
                                   Not added yet!
                                   @endif</h5>
                                </div> --}}
                                <div class="p-3 border row ">
                                    <div class="col-md-4 border-right">
                                        <h5>Registered</h5>
                                    </div>
                                    <div class="col-md-8">
                                        <h5>{{ \Carbon\Carbon::parse($user->created_at)->toDayDateTimeString() }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- signal Approval --}}
                        <div
                            style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6;">

                            @if (session('status'))
                                <div
                                    style="padding: 15px; margin-bottom: 20px; background-color: #dcfce7; color: #166534; border-radius: 8px; border: 1px solid #bbf7d0; font-weight: 500;">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <div
                                style="background: #ffffff; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #e5e7eb;">
                                <div style="padding: 20px; border-bottom: 1px solid #e5e7eb; background: #fff;">
                                    <h3 style="margin: 0; font-size: 18px; color: #111827;">Signal Purchase Requests</h3>
                                </div>

                                {{-- Responsive Scroll Wrapper --}}
                                <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                    <table
                                        style="width: 100%; border-collapse: collapse; text-align: left; min-width: 900px;">
                                        <thead>
                                            <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                                                <th
                                                    style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    User Details</th>
                                                <th
                                                    style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    Signal Plan</th>
                                                <th
                                                    style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    Amount</th>
                                                <th
                                                    style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    Duration</th>
                                                <th
                                                    style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    Status</th>
                                                <th
                                                    style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    Submitted</th>
                                                <th
                                                    style="padding: 15px 20px; color: #4b5563; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($transactions as $trx)
                                                <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.2s;">
                                                    <td style="padding: 16px 20px;">
                                                        <div style="font-weight: 600; color: #111827;">
                                                            {{ $trx->user->name }}</div>
                                                        <div style="font-size: 12px; color: #6b7280;">
                                                            {{ $trx->user->email }}</div>
                                                    </td>
                                                    <td style="padding: 16px 20px; color: #374151; font-weight: 500;">
                                                        {{ $trx->signal->name ?? 'Deleted Plan' }}
                                                    </td>
                                                    <td style="padding: 16px 20px; font-weight: 700; color: #111827;">
                                                        ${{ number_format($trx->amount, 2) }}
                                                    </td>
                                                    <td style="padding: 16px 20px; font-weight: 700; color: #111827;">
                                                        {{ $trx->signal->duration }} days
                                                    </td>
                                                    <td style="padding: 16px 20px;">
                                                        @php
                                                            $colors = [
                                                                'approved' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                                                'pending' => ['bg' => '#fef9c3', 'text' => '#854d0e'],
                                                                'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                                            ];
                                                            $current = $colors[$trx->status] ?? [
                                                                'bg' => '#f3f4f6',
                                                                'text' => '#374151',
                                                            ];
                                                        @endphp
                                                        <span
                                                            style="padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; background-color: {{ $current['bg'] }}; color: {{ $current['text'] }};">
                                                            {{ $trx->status }}
                                                        </span>
                                                    </td>
                                                    <td style="padding: 16px 20px; color: #6b7280; font-size: 13px;">
                                                        {{ $trx->created_at->diffForHumans() }}
                                                    </td>
                                                    <td style="padding: 16px 20px;">
                                                        <div style="display: flex; gap: 8px; align-items: center;">
                                                            @if ($trx->status == 'pending')
                                                                {{-- Approve Button --}}
                                                                <form
                                                                    action="{{ route('admin.signals.approve', $trx->id) }}"
                                                                    method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to approve this signal purchase?')"> 
                                                                    @csrf
                                                                    <button type="submit"
                                                                        style="background: #10b981; color: white; border: none; padding: 7px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; font-weight: 600;">
                                                                        Approve
                                                                    </button>
                                                                </form>

                                                                {{-- Decline Button --}}
                                                                <form
                                                                    action="{{ route('admin.signals.decline', $trx->id) }}"
                                                                    method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to decline this signal purchase?')">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        style="background: #f59e0b; color: white; border: none; padding: 7px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; font-weight: 600;">
                                                                        Decline
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            {{-- Delete Button (Always shows to allow resetting user status) --}}
                                                            <form action="{{ route('admin.signals.delete', $trx->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Is duration complete? Delete so the customer can purchase that package again')"
                                                                style="margin: 0;">
                                                                @csrf
                                                                <button type="submit"
                                                                    style="background: #ef4444; color: white; border: none; padding: 7px 14px; border-radius: 6px; font-size: 12px; cursor: pointer; font-weight: 600; transition: opacity 0.2s;">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Pagination --}}
                                <div style="padding: 20px; border-top: 1px solid #e5e7eb; background: #f9fafb;">
                                    {!! $transactions->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.Users.users_actions')
@endsection
