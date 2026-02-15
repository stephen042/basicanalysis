@props([
    'title' => '',
    'value' => '',
    'icon' => '',
    'iconColor' => 'primary',
    'change' => null,
    'isPositive' => true,
    'chartData' => null,
    'footer' => null,
    'isLoading' => false,
])

@php
    $colorClasses = [
        'primary' => 'text-primary-500 bg-primary-500/10',
        'success' => 'text-success-500 bg-success-500/10',
        'warning' => 'text-warning-500 bg-warning-500/10',
        'danger' => 'text-danger-500 bg-danger-500/10',
        'info' => 'text-blue-500 bg-blue-500/10',
    ];

    $iconColorClass = $colorClasses[$iconColor] ?? $colorClasses['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-dark-200 rounded-xl shadow-sm border border-dark-100 overflow-hidden']) }}>
    <div class="p-5">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-sm font-medium text-gray-400">{{ $title }}</h3>

                @if($isLoading)
                    <div class="h-8 w-20 bg-dark-100 animate-pulse rounded mt-1"></div>
                @else
                    <div class="flex items-baseline mt-1">
                        <span class="text-2xl font-semibold text-white">{{ $value }}</span>

                        @if($change !== null)
                            <span class="ml-2 text-xs {{ $isPositive ? 'text-success-500' : 'text-danger-500' }}">
                                <i class="fas fa-arrow-{{ $isPositive ? 'up' : 'down' }} mr-0.5"></i>
                                {{ $change }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            @if($icon)
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full {{ $iconColorClass }}">
                        <i class="{{ $icon }}"></i>
                    </div>
                </div>
            @endif
        </div>

        @if($chartData)
            <div class="mt-3">
                {{ $chartData }}
            </div>
        @endif
    </div>

    @if($footer)
        <div class="px-5 py-3 bg-dark-100/50 border-t border-dark-100">
            {{ $footer }}
        </div>
    @endif
</div>
