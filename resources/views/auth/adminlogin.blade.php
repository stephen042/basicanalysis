@extends('layouts.guest')
@section('title', 'Manager Login')

@section('content')
<div>
    <h2 class="mt-6 text-3xl font-extrabold text-center text-white">
        Manager Login
    </h2>
    <p class="mt-2 text-sm text-center text-gray-400">
        Please sign in to your manager account
    </p>
</div>

<form class="mt-8 space-y-6" method="POST" action="{{ route('adminlogin') }}">
    @csrf

    <x-danger-alert/>
    <x-success-alert/>

    <div class="space-y-4 rounded-md shadow-sm">
        <div>
            <label for="email-address" class="sr-only">Email address</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-envelope"></i>
                </span>
                <input id="email-address" name="email" type="email" autocomplete="email" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="Email address" value="{{ old('email') }}">
            </div>
            @error('email')
                <small class="mt-1 text-red-500">{{$message}}</small>
            @enderror
        </div>
        <div>
            <label for="password" class="sr-only">Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-lock"></i>
                </span>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="Password">
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end">
        <div class="text-sm">
            
        </div>
    </div>

    <div>
        <button type="submit"
            class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white border border-transparent rounded-md group bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
            Sign in
        </button>
    </div>

    <div class="text-sm text-center text-gray-400">
        &copy; Copyright {{ date('Y') }} {{ $settings->site_name }}. All Rights Reserved.
    </div>
</form>
@endsection
