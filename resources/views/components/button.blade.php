@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left'
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors';

    $variantClasses = [
        'primary' => 'bg-primary-600 hover:bg-primary-700 text-white focus:ring-primary-500 focus:ring-offset-dark-400',
        'secondary' => 'bg-secondary-600 hover:bg-secondary-700 text-white focus:ring-secondary-500 focus:ring-offset-dark-400',
        'success' => 'bg-success-600 hover:bg-success-700 text-white focus:ring-success-500 focus:ring-offset-dark-400',
        'danger' => 'bg-danger-600 hover:bg-danger-700 text-white focus:ring-danger-500 focus:ring-offset-dark-400',
        'warning' => 'bg-warning-600 hover:bg-warning-700 text-white focus:ring-warning-500 focus:ring-offset-dark-400',
        'outline' => 'border border-gray-600 hover:bg-dark-100 text-gray-300 focus:ring-gray-500 focus:ring-offset-dark-400',
        'ghost' => 'text-gray-300 hover:bg-dark-100 hover:text-white focus:ring-gray-500 focus:ring-offset-dark-400',
    ];

    $sizeClasses = [
        'xs' => 'px-2.5 py-1.5 text-xs',
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
        'xl' => 'px-6 py-3 text-base',
    ];

    $classes = $baseClasses . ' ' . $variantClasses[$variant] . ' ' . $sizeClasses[$size];
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon && $iconPosition === 'left')
        <i class="{{ $icon }} mr-2"></i>
    @endif

    {{ $slot }}

    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} ml-2"></i>
    @endif
</button>
