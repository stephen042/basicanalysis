@extends('layouts.dash')
@section('title', $title)
@section('content')
    <!-- Page title -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white md:text-3xl">AI Auto-Trading Plans</h1>
        <p class="mt-1 text-sm text-gray-400">Invest in an AI Plan to make your money work for you.</p>
    </div>

    <x-danger-alert />
    <x-success-alert />

    <div class="p-4 mb-8 border rounded-lg bg-dark-200 border-dark-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Available Balance</p>
                <p class="text-2xl font-bold text-white">{{ Auth::user()->currency }}{{ number_format(Auth::user()->account_bal, 2) }}</p>
            </div>
            <a href="{{ route('deposits') }}" class="px-4 py-2 text-sm font-medium text-white transition-colors bg-primary-600 rounded-md hover:bg-primary-700">
                <i class="mr-2 fas fa-plus"></i>Add Funds
            </a>
        </div>
    </div>

    @if ($uplan)
    <div class="p-4 mb-8 text-yellow-100 border border-yellow-500/30 bg-yellow-500/10 rounded-lg" role="alert">
        <div class="flex items-center">
            <i class="mr-3 fas fa-info-circle fa-lg"></i>
            <div class="flex-1">
                You are currently on a plan. To invest in another plan, please wait for your current plan to expire.
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($plans as $plan)
            <div x-data="{ investing: false, amount: '{{ $plan->min_price }}' }" class="flex flex-col bg-dark-200 rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-primary-500/20 hover:ring-2 hover:ring-primary-500">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-white">{{ $plan->name }}</h3>
                    <p class="mt-1 text-sm text-gray-400">{{ $plan->description ?? 'A robust plan for steady growth.' }}</p>
                </div>

                <div class="px-6 py-4 space-y-4 bg-dark-100/50">
                    <div class="flex justify-between">
                        <p class="text-sm text-gray-400">Minimum Amount</p>
                        <p class="font-semibold text-white">{{ Auth::user()->currency }}{{ number_format($plan->min_price) }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="text-sm text-gray-400">Maximum Amount</p>
                        <p class="font-semibold text-white">{{ Auth::user()->currency }}{{ number_format($plan->max_price) }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="text-sm text-gray-400">Return on Investment (ROI)</p>
                        <p class="font-semibold text-green-400">{{ $plan->min_return . '%' }} - {{ $plan->max_return . '%' }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="text-sm text-gray-400">Duration</p>
                        <p class="font-semibold text-white">{{ $plan->expiration }}</p>
                    </div>
                </div>

                <div class="p-6 mt-auto">
                    @if (!$uplan)
                        <div x-show="!investing">
                            <button @click="investing = true" class="w-full px-6 py-3 font-medium text-white transition-colors rounded-md bg-primary-600 hover:bg-primary-700">
                                Invest Now
                            </button>
                        </div>

                        <div x-show="investing" x-cloak>
                            <form method="post" action="{{ route('joinplan') }}">
                                @csrf
                                <label for="amount-{{ $plan->id }}" class="block mb-2 text-sm font-medium text-gray-300">Amount to Invest</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">{{ Auth::user()->currency }}</span>
                                    <input
                                        type="number"
                                        id="amount-{{ $plan->id }}"
                                        name="iamount"
                                        x-model="amount"
                                        min="{{ $plan->min_price }}"
                                        max="{{ $plan->max_price }}"
                                        class="w-full py-3 pl-10 pr-4 text-white bg-dark-100 border-transparent rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        required
                                    >
                                </div>
                                <input type="hidden" name="id" value="{{ $plan->id }}">
                                <div class="flex items-center mt-4 space-x-4">
                                    <button type="button" @click="investing = false" class="w-full px-4 py-2 text-sm font-medium text-gray-300 bg-dark-100 rounded-md hover:bg-dark-300">Cancel</button>
                                    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">Confirm & Invest</button>
                                </div>
                            </form>
                        </div>
                    @else
                        <button class="w-full px-6 py-3 font-medium text-gray-400 bg-dark-100 rounded-md cursor-not-allowed" disabled>
                            Plan Active
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-24 text-center md:col-span-2 xl:col-span-3">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-primary-400 bg-dark-100 rounded-full">
                    <i class="text-4xl fas fa-database"></i>
                </div>
                <h3 class="text-xl font-semibold text-white">No Investment Plans Available</h3>
                <p class="mt-2 text-gray-400">There are no investment plans available at the moment. Please check back later.</p>
            </div>
        @endforelse
    </div>
@endsection

