@extends('layouts.dash')
@section('title', $title)
@section('content')
    <!-- Page title & actions -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white md:text-3xl">Swap History</h1>
            <p class="mt-1 text-sm text-gray-400">View all your cryptocurrency swap transactions</p>
        </div>
        <a href="{{ route('assetbalance') }}" class="px-4 py-2 text-sm font-medium text-white transition-colors bg-dark-100 rounded-lg hover:bg-dark-300">
            <i class="mr-2 fas fa-arrow-left"></i>
            Back
        </a>
    </div>

    <x-danger-alert />
    <x-success-alert />

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-3">
        <div class="p-5 bg-dark-200 rounded-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase">Total Swaps</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $transactions->total() }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-primary-400 bg-primary-500/10 rounded-full">
                    <i class="text-xl fas fa-exchange-alt"></i>
                </div>
            </div>
        </div>

        <div class="p-5 bg-dark-200 rounded-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase">Recent Activity</p>
                    <p class="mt-2 text-lg font-semibold text-white">
                        @if($transactions->count() > 0)
                            {{ \Carbon\Carbon::parse($transactions->first()->created_at)->diffForHumans() }}
                        @else
                            No activity
                        @endif
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-primary-400 bg-primary-500/10 rounded-full">
                    <i class="text-xl fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="p-5 bg-dark-200 rounded-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase">Page {{ $transactions->currentPage() }} of {{ $transactions->lastPage() }}</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $transactions->count() }} items</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-primary-400 bg-primary-500/10 rounded-full">
                    <i class="text-xl fas fa-list"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Swap History Table -->
    <div class="p-4 bg-dark-200 sm:p-6 rounded-xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white">All Swap Transactions</h3>
            <div class="text-sm text-gray-400">
                Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
            </div>
        </div>

        @if($transactions->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-dark-100">
                    <thead class="bg-dark-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">
                                Source Asset
                            </th>
                            <th scope="col" class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">
                                Destination Asset
                            </th>
                            <th scope="col" class="px-6 py-4 text-xs font-medium tracking-wider text-right text-gray-400 uppercase">
                                Amount Sent
                            </th>
                            <th scope="col" class="px-6 py-4 text-xs font-medium tracking-wider text-right text-gray-400 uppercase">
                                Amount Received
                            </th>
                            <th scope="col" class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">
                                Date & Time
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-dark-200 divide-y divide-dark-100">
                        @foreach($transactions as $tran)
                            <tr class="transition-colors hover:bg-dark-100">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-10 h-10 mr-3 text-primary-400 bg-primary-500/10 rounded-full">
                                            <i class="fas fa-coins"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-white">{{ $tran->source }}</div>
                                            <div class="text-xs text-gray-400">From</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-10 h-10 mr-3 text-primary-400 bg-primary-500/10 rounded-full">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-white">{{ $tran->dest }}</div>
                                            <div class="text-xs text-gray-400">To</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="font-semibold text-white">{{ number_format($tran->amount, 6) }}</div>
                                    <div class="text-xs text-gray-400">{{ $tran->source }}</div>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="font-semibold text-primary-400">{{ number_format($tran->quantity, 6) }}</div>
                                    <div class="text-xs text-gray-400">{{ $tran->dest }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-white">{{ \Carbon\Carbon::parse($tran->created_at)->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($tran->created_at)->format('h:i A') }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="space-y-4 lg:hidden">
                @foreach($transactions as $tran)
                    <div class="p-4 transition-colors bg-dark-100 rounded-lg hover:bg-dark-300">
                        <!-- Swap Direction -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center flex-1">
                                <div class="flex items-center justify-center w-10 h-10 mr-3 text-primary-400 bg-primary-500/10 rounded-full">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-white">{{ $tran->source }}</div>
                                    <div class="text-xs text-gray-400">From</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-center w-8 h-8 mx-2 text-gray-400">
                                <i class="fas fa-arrow-right"></i>
                            </div>

                            <div class="flex items-center flex-1">
                                <div class="flex items-center justify-center w-10 h-10 mr-3 text-primary-400 bg-primary-500/10 rounded-full">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-white">{{ $tran->dest }}</div>
                                    <div class="text-xs text-gray-400">To</div>
                                </div>
                            </div>
                        </div>

                        <!-- Amounts -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-dark-300">
                            <div>
                                <p class="text-xs text-gray-400">Amount Sent</p>
                                <p class="font-semibold text-white">{{ number_format($tran->amount, 6) }}</p>
                                <p class="text-xs text-gray-400">{{ $tran->source }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-400">Amount Received</p>
                                <p class="font-semibold text-primary-400">{{ number_format($tran->quantity, 6) }}</p>
                                <p class="text-xs text-gray-400">{{ $tran->dest }}</p>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="flex items-center justify-between pt-3 mt-3 border-t border-dark-300">
                            <div class="flex items-center text-xs text-gray-400">
                                <i class="mr-2 fas fa-clock"></i>
                                {{ \Carbon\Carbon::parse($tran->created_at)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($tran->created_at)->format('h:i A') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex flex-col items-center justify-between gap-4 pt-6 mt-6 border-t sm:flex-row border-dark-100">
                <div class="text-sm text-gray-400">
                    Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
                </div>
                <div class="flex gap-2">
                    {{ $transactions->links() }}
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="py-16 text-center">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 text-gray-600 bg-dark-100 rounded-full">
                    <i class="text-3xl fas fa-exchange-alt"></i>
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-400">No Swap History</h3>
                <p class="mb-6 text-gray-500">You haven't made any cryptocurrency swaps yet.</p>
                <a href="{{ route('assetbalance') }}" class="inline-flex items-center px-6 py-3 text-sm font-medium text-white transition-colors bg-primary-600 rounded-lg hover:bg-primary-700">
                    <i class="mr-2 fas fa-exchange-alt"></i>
                    Start Swapping
                </a>
            </div>
        @endif
    </div>
@endsection