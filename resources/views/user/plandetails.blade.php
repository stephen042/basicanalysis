@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div x-data="{ cancelModal: false }">
        <!-- Page title -->
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <a href="{{ route('myplans', 'All') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to My Plans</span>
                </a>
                <h1 class="mt-2 text-2xl font-bold text-white md:text-3xl">
                    {{ $plan->dplan->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-400">
                    {{ $plan->dplan->increment_type == 'Fixed' ? Auth::user()->currency : '' }}{{ $plan->dplan->increment_amount }}{{ $plan->dplan->increment_type == 'Percentage' ? '%' : '' }}
                    {{ $plan->dplan->increment_interval }} for {{ $plan->dplan->expiration }}
                </p>
            </div>
            <div class="flex items-center gap-4">
                @if ($plan->active == 'yes')
                    <span class="px-3 py-1 text-xs font-medium text-green-400 bg-green-500/10 rounded-full">
                        <i class="mr-1 fas fa-check-circle"></i>Active
                    </span>
                @elseif($plan->active == 'expired')
                    <span class="px-3 py-1 text-xs font-medium text-red-400 bg-red-500/10 rounded-full">
                        <i class="mr-1 fas fa-times-circle"></i>Expired
                    </span>
                @else
                    <span class="px-3 py-1 text-xs font-medium text-gray-400 bg-gray-500/10 rounded-full">
                        <i class="mr-1 fas fa-ban"></i>Inactive
                    </span>
                @endif

                @if ($settings->should_cancel_plan && $plan->active == 'yes')
                    <button @click="cancelModal = true" class="px-3 py-1 text-xs font-medium text-red-400 transition-colors bg-red-500/10 rounded-full hover:bg-red-500/20">
                        <i class="mr-1 fas fa-times"></i>
                        <span>Cancel Plan</span>
                    </button>
                @endif
            </div>
        </div>

        <x-danger-alert />
        <x-success-alert />

        <!-- Financial Overview -->
        <div class="grid grid-cols-1 gap-6 mt-8 md:grid-cols-3">
            <div class="p-6 bg-dark-200 rounded-xl">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 text-blue-400 rounded-full bg-dark-300">
                        <i class="text-xl fas fa-wallet"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Invested Amount</p>
                        <p class="text-2xl font-semibold text-white">
                            {{ Auth::user()->currency }}{{ number_format($plan->amount, 2) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-dark-200 rounded-xl">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 text-green-400 rounded-full bg-dark-300">
                        <i class="text-xl fas fa-chart-line"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Profit Earned</p>
                        <p class="text-2xl font-semibold text-green-400">
                           + {{ Auth::user()->currency }}{{ number_format($plan->profit_earned, 2) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-dark-200 rounded-xl">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full text-primary-400 bg-dark-300">
                        <i class="text-xl fas fa-sack-dollar"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Total Return</p>
                        <p class="text-2xl font-semibold text-white">
                            @if ($settings->return_capital)
                                {{ Auth::user()->currency }}{{ number_format($plan->amount + $plan->profit_earned, 2) }}
                            @else
                                {{ Auth::user()->currency }}{{ number_format($plan->profit_earned, 2) }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan Details & Transactions -->
        <div class="grid grid-cols-1 gap-8 mt-8 lg:grid-cols-3">
            <!-- Left Column: Plan Info -->
            <div class="p-6 lg:col-span-1 bg-dark-200 rounded-xl h-fit">
                <h3 class="text-lg font-semibold text-white">Plan Information</h3>
                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Duration</dt>
                        <dd class="font-medium text-white">{{ $plan->dplan->expiration }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Start Date</dt>
                        <dd class="font-medium text-white">{{ $plan->created_at->toFormattedDateString() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">End Date</dt>
                        <dd class="font-medium text-white">{{ \Carbon\Carbon::parse($plan->expire_date)->toFormattedDateString() }}</dd>
                    </div>
                    <div class="pt-4 border-t border-dark-100"></div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Min/Max Return</dt>
                        <dd class="font-medium text-white">{{ $plan->dplan->minr }}% - {{ $plan->dplan->maxr }}%</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">ROI Interval</dt>
                        <dd class="font-medium text-white">{{ $plan->dplan->increment_interval }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Right Column: Transactions -->
            <div class="lg:col-span-2">
                <h3 class="text-lg font-semibold text-white">Profit Transactions</h3>
                <div class="mt-4 overflow-x-auto bg-dark-200 rounded-xl">
                    <table class="w-full text-sm text-left text-gray-400">
                        <thead class="text-xs text-gray-400 uppercase bg-dark-300">
                            <tr>
                                <th scope="col" class="px-6 py-3">Type</th>
                                <th scope="col" class="px-6 py-3">Date</th>
                                <th scope="col" class="px-6 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $history)
                                <tr class="border-b bg-dark-200 border-dark-100 hover:bg-dark-100/50">
                                    <td class="px-6 py-4 font-medium text-white">Profit</td>
                                    <td class="px-6 py-4">{{ $history->created_at->toDayDateTimeString() }}</td>
                                    <td class="px-6 py-4 font-medium text-right text-green-400">
                                        +{{ Auth::user()->currency }}{{ number_format($history->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-12 text-center">
                                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-primary-400 bg-dark-300 rounded-full">
                                            <i class="text-2xl fas fa-receipt"></i>
                                        </div>
                                        <h4 class="text-lg font-medium text-white">No transactions yet</h4>
                                        <p class="text-gray-500">Profit payouts will appear here.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($transactions->hasPages())
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Cancel Plan Modal -->
        <div x-show="cancelModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75" style="display: none;">
            <div @click.away="cancelModal = false" class="w-full max-w-md mx-auto bg-dark-200 rounded-2xl shadow-xl">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-red-500/10 rounded-full">
                            <i class="text-xl text-red-400 fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white" id="modal-title">Cancel Investment Plan</h3>
                            <p class="mt-2 text-sm text-gray-400">
                                Are you sure you want to cancel your <strong>{{ $plan->dplan->name }}</strong> plan? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-dark-300/50 rounded-b-2xl">
                    <button type="button" @click="cancelModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-300 bg-transparent border border-gray-600 rounded-lg hover:bg-gray-700">
                        Nevermind
                    </button>
                    <a href="{{ route('cancelplan', $plan->id) }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Yes, Cancel Plan
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
