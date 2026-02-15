@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div x-data="{ withdrawDisabledModal: false }">
        <!-- Page title -->
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-white md:text-3xl">Request Withdrawal</h1>
                <p class="mt-1 text-sm text-gray-400">Select a method to withdraw your funds.</p>
            </div>
        </div>

        <x-danger-alert />
        <x-success-alert />

        <div class="grid grid-cols-1 gap-6 mt-8 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($wmethods as $method)
                <div class="flex flex-col p-6 transition-all duration-300 bg-dark-200 rounded-xl hover:bg-dark-100 hover:shadow-lg">
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ $method->name }}</h3>
                                <p class="text-sm text-gray-400">Duration: {{ $method->duration }}</p>
                            </div>
                            <div class="flex items-center justify-center w-12 h-12 text-xl rounded-full bg-dark-300 text-primary-400">
                                {{-- You can use specific icons/images here if available --}}
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4 text-sm">
                            <div class="p-3 rounded-lg bg-dark-300/50">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Minimum</span>
                                    <span class="font-medium text-white">{{ Auth::user()->currency }}{{ number_format($method->minimum) }}</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-lg bg-dark-300/50">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Maximum</span>
                                    <span class="font-medium text-white">{{ Auth::user()->currency }}{{ number_format($method->maximum) }}</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-lg bg-dark-300/50">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Charge</span>
                                    <span class="font-medium text-white">
                                        @if ($method->charges_type == 'percentage')
                                            {{ $method->charges_amount }}%
                                        @else
                                            {{ Auth::user()->currency }}{{ $method->charges_amount }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        @if ($settings->enable_with == 'false')
                            <button @click="withdrawDisabledModal = true"
                                class="w-full px-4 py-2.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                                Request Withdrawal
                            </button>
                        @else
                            <form action='{{ route('withdrawamount') }}' method="POST">
                                @csrf
                                <input type="hidden" value="{{ $method->name }}" name="method">
                                <button type="submit"
                                    class="w-full px-4 py-2.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                                    Request Withdrawal
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-24 text-center md:col-span-2 lg:col-span-3">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-primary-400 bg-dark-100 rounded-full">
                        <i class="text-4xl fas fa-exclamation-circle"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white">No Withdrawal Methods Available</h3>
                    <p class="mt-2 text-gray-400">There are currently no methods available for withdrawal. Please check back later.</p>
                </div>
            @endforelse
        </div>

        <!-- Withdrawal Disabled Modal -->
        <div x-show="withdrawDisabledModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75" style="display: none;">
            <div @click.away="withdrawDisabledModal = false" class="w-full max-w-md mx-auto bg-dark-200 rounded-2xl shadow-xl">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-yellow-500/10 rounded-full">
                            <i class="text-xl text-yellow-400 fas fa-info-circle"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white" id="modal-title">Withdrawal Unavailable</h3>
                            <p class="mt-2 text-sm text-gray-400">
                                Withdrawals are temporarily disabled by the administrator. Please check back later.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-dark-300/50 rounded-b-2xl">
                    <button type="button" @click="withdrawDisabledModal = false"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-600 hover:bg-primary-700">
                        Understood
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
