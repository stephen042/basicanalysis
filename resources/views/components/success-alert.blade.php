@if (Session::has('success'))
    <div x-data="{ show: true }" x-show="show" class="flex items-center p-4 mb-4 text-sm text-green-100 bg-green-500/80 rounded-lg backdrop-blur-sm" role="alert">
        <i class="mr-3 fas fa-check-circle fa-lg"></i>
        <span class="flex-1">{{ Session::get('success') }}</span>
        <button @click="show = false" type="button" class="ml-4 -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-600/50 inline-flex h-8 w-8 text-green-100">
            <span class="sr-only">Dismiss</span>
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

