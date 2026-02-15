@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div class="space-y-8" x-data="{ tab: 'deposits' }">
        <div>
            <h1 class="text-3xl font-bold text-white">Transaction History</h1>
            <p class="mt-1 text-gray-400">Review your deposit, withdrawal, and other transaction records.</p>
        </div>

        <x-danger-alert />
        <x-success-alert />

        <div class="p-4 rounded-lg sm:p-6 bg-dark-200">
            <!-- Tab Buttons -->
            <div class="grid grid-cols-3 gap-2 mb-6">
                <button @click="tab = 'deposits'"
                    :class="{ 'bg-primary-600 text-white': tab === 'deposits', 'bg-dark-300 text-gray-300 hover:bg-dark-100': tab !== 'deposits' }"
                    class="flex items-center justify-center px-4 py-3 font-semibold text-center transition-colors duration-200 rounded-lg">
                 <i class="fas fa-download text-lg sm:mr-2"></i>

                    <span class="hidden sm:inline">Deposits</span>
                </button>
                <button @click="tab = 'withdrawals'"
                    :class="{ 'bg-primary-600 text-white': tab === 'withdrawals', 'bg-dark-300 text-gray-300 hover:bg-dark-100': tab !== 'withdrawals' }"
                    class="flex items-center justify-center px-4 py-3 font-semibold text-center transition-colors duration-200 rounded-lg">
                    <i class="text-lg fas fa-arrow-up-from-bracket sm:mr-2"></i>
                    <span class="hidden sm:inline">Withdrawals</span>
                </button>
                <button @click="tab = 'others'"
                    :class="{ 'bg-primary-600 text-white': tab === 'others', 'bg-dark-300 text-gray-300 hover:bg-dark-100': tab !== 'others' }"
                    class="flex items-center justify-center px-4 py-3 font-semibold text-center transition-colors duration-200 rounded-lg">
                    <i class="text-lg fas fa-exchange-alt sm:mr-2"></i>
                    <span class="hidden sm:inline">Others</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="overflow-x-auto">
                <!-- Deposits Table -->
                <div x-show="tab === 'deposits'" x-cloak>
                    <table class="min-w-full divide-y divide-dark-300">
                        <thead class="bg-dark-300">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Amount</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Payment Mode</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Status</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y bg-dark-200 divide-dark-300">
                            @forelse ($deposits as $deposit)
                                <tr class="hover:bg-dark-100">
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ Auth::user()->currency }}{{ number_format($deposit->amount) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-300">{{ $deposit->payment_mode }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($deposit->status == 'Processed')
                                            <span class="px-2.5 py-1 text-xs font-semibold text-green-800 bg-green-200 rounded-full">{{ $deposit->status }}</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold text-red-800 bg-red-200 rounded-full">{{ $deposit->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-400">{{ \Carbon\Carbon::parse($deposit->created_at)->toDayDateTimeString() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-400">No deposits found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Withdrawals Table -->
                <div x-show="tab === 'withdrawals'" x-cloak>
                    <table class="min-w-full divide-y divide-dark-300">
                        <thead class="bg-dark-300">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Amount Requested</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Amount + Charges</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Receiving Mode</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Status</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y bg-dark-200 divide-dark-300">
                            @forelse ($withdrawals as $withdrawal)
                                <tr class="hover:bg-dark-100">
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ Auth::user()->currency }}{{ number_format($withdrawal->amount) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ Auth::user()->currency }}{{ number_format($withdrawal->to_deduct) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-300">{{ $withdrawal->payment_mode }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($withdrawal->status == 'Processed')
                                            <span class="px-2.5 py-1 text-xs font-semibold text-green-800 bg-green-200 rounded-full">{{ $withdrawal->status }}</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold text-red-800 bg-red-200 rounded-full">{{ $withdrawal->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-400">{{ \Carbon\Carbon::parse($withdrawal->created_at)->toDayDateTimeString() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400">No withdrawals found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Others Table -->
                <div x-show="tab === 'others'" x-cloak>
                    <table class="min-w-full divide-y divide-dark-300">
                        <thead class="bg-dark-300">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Amount</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Type</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Plan/Narration</th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y bg-dark-200 divide-dark-300">
                            @forelse ($t_history as $history)
                                <tr class="hover:bg-dark-100">
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ Auth::user()->currency }}{{ number_format($history->amount) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-300">{{ $history->type }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-300">{{ $history->plan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-400">{{ \Carbon\Carbon::parse($history->created_at)->toDayDateTimeString() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-400">No other transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

