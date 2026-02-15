@extends('layouts.guest')
@section('title', 'Reset Your Password')

@section('content')
<div>
    <h2 class="mt-6 text-3xl font-extrabold text-center text-white">
        Reset Manager Password
    </h2>
    <p class="mt-2 text-sm text-center text-gray-400">
        Create a new password for your manager account.
    </p>
</div>

<form class="mt-8 space-y-6" method="POST" action="{{ route('restpass') }}">
    @csrf
    <x-error-alert />
    <x-danger-alert/>
    <x-success-alert />

    <div class="space-y-4 rounded-md shadow-sm">
        <div>
            <label for="email" class="sr-only">Email address</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-envelope"></i>
                </span>
                <input id="email" name="email" type="email" readonly
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-200 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    value="{{ $email }}">
            </div>
            @error('email')
                <small class="mt-1 text-red-500">{{ $message }}</small>
            @enderror
        </div>
        <div>
            <label for="token" class="sr-only">Token</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-hashtag"></i>
                </span>
                <input id="token" name="token" type="number"
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="Token">
            </div>
             @error('token')
                <small class="mt-1 text-red-500">{{ $message }}</small>
            @enderror
        </div>
        <div>
            <label for="password" class="sr-only">New Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-lock"></i>
                </span>
                <input id="password" name="password" type="password"
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="New Password">
            </div>
            @error('password')
                <small class="mt-1 text-red-500">{{ $message }}</small>
            @enderror
        </div>
        <div>
            <label for="password_confirmation" class="sr-only">Confirm New Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-lock"></i>
                </span>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="Confirm New Password">
            </div>
             @error('password_confirmation')
                <small class="mt-1 text-red-500">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div>
        <button type="submit"
            class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white border border-transparent rounded-md group bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
            Reset Password
        </button>
    </div>

    <div class="text-sm text-center text-gray-400">
        &copy; Copyright {{ date('Y') }} {{ $settings->site_name }}. All Rights Reserved.
    </div>
</form>
@endsection
