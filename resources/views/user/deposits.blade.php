@extends('layouts.dash')
@section('title', $title)
@section('content')
    <!-- Page title -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white md:text-3xl">Fund Your Account</h1>
        <p class="mt-1 text-sm text-gray-400">Choose a payment method and enter the amount you wish to deposit.</p>
    </div>

    <x-danger-alert />
    <x-success-alert />

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">

        {{-- Deposit Form --}}
        <div class="lg:col-span-7">
            <div class="p-6 bg-dark-200 rounded-xl" x-data="{ amount: '{{ $moresettings->minamt }}', selectedMethod: '', selectedMethodName: '' }">
                <form action="{{ route('newdeposit') }}" method="post" id="submitpaymentform">
                    @csrf
                    <input type="hidden" name="payment_method" x-model="selectedMethodName">

                    {{-- Amount Input --}}
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-300">Enter Amount</label>
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <span class="text-gray-400">{{ Auth::user()->currency }}</span>
                            </div>
                            <input type="number" name="amount" id="amount" x-model="amount"
                                min="{{ $moresettings->minamt }}"
                                class="w-full py-3 pl-10 pr-4 text-white bg-dark-100 border-transparent rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                placeholder="0.00" required>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Minimum deposit: {{ Auth::user()->currency }}{{ $moresettings->minamt }}</p>
                    </div>

                    {{-- Payment Methods --}}
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-300">Choose Payment Method</h3>
                        <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                            @forelse ($dmethods as $method)
                                <div @click="selectedMethod = '{{ $method->id }}'; selectedMethodName = '{{ $method->name }}'"
                                    :class="selectedMethod === '{{ $method->id }}' ? 'ring-2 ring-primary-500' : 'ring-1 ring-dark-100'"
                                    class="relative flex items-center p-4 transition-all bg-dark-100 rounded-lg cursor-pointer hover:bg-dark-300">
                                    @if (!empty($method->img_url))
                                        <img src="{{ $method->img_url }}" alt="{{ $method->name }}" class="w-8 h-8 mr-4">
                                    @else
                                        <div class="flex items-center justify-center w-8 h-8 mr-4 rounded-full bg-dark-300">
                                            <i class="fas fa-money-check-alt text-primary-400"></i>
                                        </div>
                                    @endif
                                    <span class="flex-1 font-semibold text-white">{{ $method->name }}</span>
                                    <div x-show="selectedMethod === '{{ $method->id }}'" class="text-primary-500" x-cloak>
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 sm:col-span-2">No payment methods are available at the moment.</p>
                            @endforelse
                        </div>
                    </div>

                    @if (count($dmethods) > 0)
                        <div class="pt-5 mt-5 border-t border-dark-100">
                            <button type="submit"
                                class="w-full px-6 py-3 text-sm font-medium text-white transition-colors bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-dark-200 focus:ring-primary-500 disabled:opacity-50"
                                :disabled="!selectedMethod || !amount">
                                Proceed to Payment
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Side Panel --}}
        <div class="space-y-8 lg:col-span-5">
            <div class="p-6 bg-dark-200 rounded-xl">
                <h3 class="mb-4 text-lg font-semibold text-white">Deposit Summary</h3>
                <div class="pb-4 border-b border-dark-100">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-400">Total Deposited</p>
                        <p class="text-xl font-bold text-white">{{ Auth::user()->currency }}{{ number_format($deposited, 2, '.', ',') }}</p>
                    </div>
                </div>
                <div class="pt-4">
                     <a href="{{ route('accounthistory') }}" class="flex items-center justify-between text-sm font-medium text-primary-500 hover:text-primary-400">
                        <span>View full deposit history</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush

