@props(['title' => '', 'description' => '', 'action' => ''])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            @if($title)
                <h2 class="text-lg font-bold text-white">{{ $title }}</h2>
            @endif

            @if($description)
                <p class="mt-1 text-sm text-gray-400">{{ $description }}</p>
            @endif
        </div>

        @if($action)
            <div class="mt-3 sm:mt-0">
                {{ $action }}
            </div>
        @endif
    </div>

    <div class="mt-5">
        {{ $slot }}
    </div>
</div>
