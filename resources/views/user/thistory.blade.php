@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-bold text-white">ROI History</h1>
            <p class="mt-1 text-gray-400">A complete record of your return on investment earnings.</p>
        </div>

        <x-danger-alert />
        <x-success-alert />

        <div class="p-4 rounded-lg sm:p-6 bg-dark-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-dark-300">
                    <thead class="bg-dark-300">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">
                                Plan</th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">
                                Amount</th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">
                                Type</th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">
                                Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-dark-200 divide-dark-300">
                        @forelse ($t_history as $history)
                            <tr class="hover:bg-dark-100">
                                <td class="px-6 py-4 text-sm text-white whitespace-nowrap">
                                    {{ $history->plan }}
                                </td>
                                <td class="px-6 py-4 text-sm text-white whitespace-nowrap">
                                    {{ $settings->currency }}{{ number_format($history->amount, 2, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300 whitespace-nowrap">
                                    @if ($history->type == 'ROI')
                                        <span
                                            class="px-2.5 py-1 text-xs font-semibold text-blue-800 bg-blue-200 rounded-full">
                                            {{ $history->type }}
                                        </span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 text-xs font-semibold text-yellow-800 bg-yellow-200 rounded-full">
                                            {{ $history->type }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($history->created_at)->toDayDateTimeString() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-gray-400">
                                    You have no ROI history yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($t_history->hasPages())
                <div class="p-4 mt-4">
                    {{ $t_history->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

