@extends('layouts.dash')
@section('title', $title)
@section('content')
    <!-- Page title -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-white md:text-3xl">My Investment Plans</h1>
            <p class="mt-1 text-sm text-gray-400">
                Viewing <span class="font-semibold text-white">{{ request()->route('sort') == 'yes' ? 'Active' : ucfirst(request()->route('sort')) }}</span> plans.
            </p>
        </div>
        @if ($numOfPlan > 0)
            <div class="flex items-center gap-4">
                <form id="sortForm" action="{{ url('/dashboard/sort-plans') }}" method="GET">
                    <select name="sort" id="sortvalue" onchange="document.getElementById('sortForm').submit()"
                        class="w-full px-4 py-2 text-sm text-white bg-dark-100 border-transparent rounded-md sm:w-auto focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="All" @if(request()->route('sort') == 'All') selected @endif>All Plans</option>
                        <option value="yes" @if(request()->route('sort') == 'yes') selected @endif>Active</option>
                        <option value="cancelled" @if(request()->route('sort') == 'cancelled') selected @endif>Cancelled/Inactive</option>
                        <option value="expired" @if(request()->route('sort') == 'expired') selected @endif>Expired</option>
                    </select>
                </form>
                 <a href="{{ route('mplans') }}" class="hidden px-4 py-2 text-sm font-medium text-white transition-colors sm:block bg-primary-600 rounded-md hover:bg-primary-700">
                    <i class="mr-2 fas fa-plus"></i> New Plan
                </a>
            </div>
        @endif
    </div>

    <x-danger-alert />
    <x-success-alert />

    <div class="mt-8 space-y-6">
        @forelse ($plans as $plan)
            <div class="p-4 transition-all duration-300 bg-dark-200 rounded-xl hover:bg-dark-100 hover:shadow-lg">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 text-xl rounded-full bg-dark-300 text-primary-400">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white">{{ $plan->dplan->name }}</h3>
                            <p class="text-sm text-gray-400">
                                Amount: <span class="font-medium text-white">{{ Auth::user()->currency }}{{ number_format($plan->amount) }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="grid flex-1 grid-cols-2 gap-4 text-sm text-center sm:flex sm:justify-around sm:flex-1">
                        <div class="p-2 rounded-md bg-dark-300/50">
                            <p class="text-xs text-gray-400">Start Date</p>
                            <p class="font-semibold text-white">{{ $plan->created_at->toFormattedDateString() }}</p>
                        </div>
                        <div class="p-2 rounded-md bg-dark-300/50">
                            <p class="text-xs text-gray-400">End Date</p>
                            <p class="font-semibold text-white">{{ \Carbon\Carbon::parse($plan->expire_date)->toFormattedDateString() }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 sm:justify-end">
                         <div class="text-center">
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
                        </div>
                        <a href="{{ route('plandetails', $plan->id) }}" class="p-3 text-gray-400 rounded-full bg-dark-300 hover:bg-primary-600 hover:text-white">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                 @if($plan->active == 'yes')
                <div class="pt-4 mt-4 border-t border-dark-100">
                    <a href="{{ route('mplans') }}" class="flex items-center justify-center w-full gap-2 text-sm font-medium text-center transition-colors sm:w-auto sm:inline-flex text-primary-500 hover:text-primary-400">
                        <i class="fas fa-level-up-alt"></i> Upgrade Plan
                    </a>
                </div>
                @endif
            </div>
        @empty
            <div class="py-24 text-center">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-primary-400 bg-dark-100 rounded-full">
                    <i class="text-4xl fas fa-folder-open"></i>
                </div>
                <h3 class="text-xl font-semibold text-white">No Investment Plans Found</h3>
                <p class="mt-2 text-gray-400">You do not have any plans matching this criteria.</p>
                <a href="{{ route('mplans') }}" class="inline-block px-6 py-2 mt-6 text-sm font-medium text-white transition-colors bg-primary-600 rounded-lg hover:bg-primary-700">
                    Invest Now
                </a>
            </div>
        @endforelse

        @if (count($plans) > 0)
            <div class="mt-8">
                {{ $plans->links() }}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        // The form now submits on its own, but if you wanted to use JS:
        // document.getElementById('sortvalue').addEventListener('change', function() {
        //     let sortRoute = "{{ url('/dashboard/sort-plans') }}/" + this.value;
        //     window.location.href = sortRoute;
        // });
    </script>
@endsection
