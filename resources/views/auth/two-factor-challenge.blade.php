@extends('layouts.guest')

@section('title', 'TwoFactor Login')

@section('styles')
@parent
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.1/dist/alpine.min.js" defer></script>
@endsection
@section('content')
@extends('layouts.guest')

@section('title', 'Two-Factor Authentication')

@section('content')
<div x-data="{ recovery: false }">
    <div>
        <h2 class="mt-6 text-3xl font-extrabold text-center text-white">
            Two-Factor Challenge
        </h2>
        <p class="mt-2 text-sm text-center text-gray-400" x-show="!recovery">
            Please confirm access to your account by entering the authentication code provided by your authenticator application.
        </p>
        <p class="mt-2 text-sm text-center text-gray-400" x-show="recovery">
            Please confirm access to your account by entering one of your emergency recovery codes.
        </p>
    </div>

    <form class="mt-8 space-y-6" method="POST" action="{{ route('two-factor.login') }}">
        @csrf

        @if ($errors->any())
            <div class="p-4 text-sm text-red-300 bg-red-500 rounded-md bg-opacity-10">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-md shadow-sm" x-show="!recovery">
            <div>
                <label for="code" class="sr-only">Authentication Code</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="text-gray-400 fa-solid fa-shield-halved"></i>
                    </span>
                    <input id="code" name="code" type="text" inputmode="numeric" autofocus x-ref="code" autocomplete="one-time-code"
                        class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                        placeholder="Authentication Code">
                </div>
            </div>
        </div>

        <div class="rounded-md shadow-sm" x-show="recovery">
            <div>
                <label for="recovery_code" class="sr-only">Recovery Code</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="text-gray-400 fa-solid fa-key"></i>
                    </span>
                    <input id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code" x-ref="recovery_code"
                        class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                        placeholder="Recovery Code">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <button type="button" class="text-sm font-medium text-gray-400 hover:text-primary"
                x-show="!recovery"
                x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                Use a recovery code
            </button>

            <button type="button" class="text-sm font-medium text-gray-400 hover:text-primary"
                x-show="recovery"
                x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                Use an authentication code
            </button>
        </div>

        <div>
            <button type="submit"
                class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white border border-transparent rounded-md group bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
                Log in
            </button>
        </div>

        <div class="text-sm text-center text-gray-400">
            &copy; Copyright {{ date('Y') }} {{ $settings->site_name }}. All Rights Reserved.
        </div>
    </form>
</div>
@endsection


@endsection

@section('scripts')
@parent

@endsection
