@props(['href', 'active' => false, 'icon' => ''])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 rounded-md'
            : 'flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-dark-100 hover:text-white';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <i class="w-5 h-5 mr-3 fas {{ $icon }}"></i>
    @endif
    {{ $slot }}
</a>
