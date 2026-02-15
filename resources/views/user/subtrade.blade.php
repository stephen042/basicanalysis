@php
$sub_link = 'https://trade.mql5.com/trade';
@endphp

@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="container px-4 py-6 mx-auto" x-data="{ 'showModal': false }">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-white md:text-3xl">AI Account Manager</h1>
    </div>

    <x-danger-alert />
    <x-success-alert />

    <!-- Subscription Intro -->
    <div class="p-6 mb-8 rounded-lg shadow-lg bg-dark-200 lg:p-8">
        <div class="grid gap-8 md:grid-cols-12">
            <div class="md:col-span-8">
                <h2 class="mb-3 text-xl font-bold text-white">Let Our AI Trade For You</h2>
                <p class="text-gray-400">
                    Don’t have time to trade or learn how to trade? Our Account Management Service is the perfect solution. We manage your account in the financial markets through a simple subscription model, allowing you to benefit from expert trading without the hands-on effort.
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    Terms and Conditions apply. For more info, reach us at <a href="mailto:{{ $settings->contact_email }}" class="text-primary hover:underline">{{ $settings->contact_email }}</a>.
                </p>
            </div>
            <div class="flex items-center justify-center md:col-span-4">
                <button @click="showModal = true" class="w-full px-6 py-3 font-semibold text-white rounded-lg bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-dark-200 focus:ring-primary">
                    Subscribe Now
                </button>
            </div>
        </div>
    </div>

    <!-- Trading Accounts -->
    <div class="mb-8">
        <h3 class="mb-4 text-xl font-semibold text-white">My Trading Accounts</h3>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($subscriptions as $sub)
                <div class="p-5 border shadow-lg rounded-xl bg-dark-200 border-dark-100">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-white">{{ $sub->mt4_id }}/{{ $sub->account_type }}</h4>
                        @if ($sub->status == 'Active')
                            <span class="px-2 py-1 text-xs font-medium text-green-400 bg-green-500/10 rounded-full">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium text-red-400 bg-red-500/10 rounded-full">{{$sub->status}}</span>
                        @endif
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Currency:</span>
                            <span class="font-medium text-white">{{ $sub->currency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Leverage:</span>
                            <span class="font-medium text-white">{{ $sub->leverage }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Server:</span>
                            <span class="font-medium text-white">{{ $sub->server }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Duration:</span>
                            <span class="font-medium text-white">{{ $sub->duration }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Password:</span>
                            <span class="font-medium text-white">**********</span>
                        </div>
                    </div>
                    <div class="p-3 mt-4 text-xs text-center rounded-lg bg-dark-100">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-400">Subscribed:</span>
                            <span class="font-medium text-gray-300">{{ \Carbon\Carbon::parse($sub->created_at)->toFormattedDateString() }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-400">Started:</span>
                            <span class="font-medium text-gray-300">
                                @if (!empty($sub->start_date))
                                    {{ \Carbon\Carbon::parse($sub->start_date)->toFormattedDateString() }}
                                @else
                                    Pending
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Expires:</span>
                            <span class="font-medium text-gray-300">
                                @if (!empty($sub->end_date))
                                    {{ \Carbon\Carbon::parse($sub->end_date)->toFormattedDateString() }}
                                @else
                                    Pending
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="flex mt-4 space-x-2">
                        <button onclick="deletemt4()" class="w-full px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                            Cancel
                        </button>
                        @php
                            $remindAt = \Carbon\Carbon::parse($sub->reminded_at);
                        @endphp
                        @if (now()->isSameDay($remindAt) || $sub->status == 'Expired')
                            <a href="{{ route('renewsub', $sub->id) }}" class="w-full px-4 py-2 text-sm font-semibold text-center text-white bg-green-600 rounded-lg hover:bg-green-700">
                                Renew
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center sm:col-span-2 lg:col-span-3">
                    <div class="flex justify-center">
                        <i class="text-5xl text-gray-500 fas fa-folder-open"></i>
                    </div>
                    <p class="mt-4 text-gray-400">You do not have any trading account subscriptions at the moment.</p>
                    <button @click="showModal = true" class="px-5 py-2 mt-4 text-sm font-medium text-white rounded-lg bg-primary hover:bg-primary-dark">
                        Subscribe Today
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- WebTrader Iframe -->
    <div class="p-4 rounded-lg shadow-lg bg-dark-200 sm:p-6">
        <h3 class="mb-4 text-xl font-semibold text-white">Live Account Monitoring</h3>
        <div class="overflow-hidden rounded-lg">
            <iframe src="{{ $sub_link }}" name="WebTrader" title="{{ $title }}" class="w-full border-none h-96" style="height: 75vh;"></iframe>
        </div>
    </div>

    <!-- Subscription Modal -->
    @include('user.modals')
</div>

<script type="text/javascript">
    function deletemt4() {
        swal({
            title: "Cancel Subscription",
            text: "To cancel your subscription, please send an email to {{ $settings->contact_email }}.",
            icon: "info",
            buttons: {
                confirm: {
                    text: "Okay",
                    value: true,
                    visible: true,
                    className: "btn btn-primary",
                    closeModal: true
                }
            }
        });
    }
</script>
@endsection

