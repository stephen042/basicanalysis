 @if (Auth::user()->action == 'Yes')
    <div x-data="{ show: true }" x-show="show" class="flex items-center p-4 mb-4 text-sm text-yellow-100 bg-yellow-500/90 rounded-lg backdrop-blur-sm" role="alert">
        <i class="mr-3 fas fa-exclamation-triangle fa-lg"></i>
        <div class="flex-1">
            <span class="font-medium">Action Required!</span> You must upgrade your account with ({{ $settings->currency }}{{ Auth::user()->amount }}) immediately.
        </div>
        <button @click="show = false" type="button" class="ml-4 -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-yellow-400 p-1.5 hover:bg-yellow-600/50 inline-flex h-8 w-8 text-yellow-100">
            <span class="sr-only">Dismiss</span>
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

