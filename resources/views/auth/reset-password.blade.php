@extends('layouts.guest')

@section('title', 'Reset Your Password')

@section('content')
<div>
    <h2 class="mt-6 text-3xl font-extrabold text-center text-white">
        Create a new password
    </h2>
</div>

<form class="mt-8 space-y-6" method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    @if ($errors->any())
        <div class="p-4 text-sm text-red-300 bg-red-500 rounded-md bg-opacity-10">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-4 rounded-md shadow-sm">
        <div>
            <label for="email" class="sr-only">Email address</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-envelope"></i>
                </span>
                <input id="email" name="email" type="email" autocomplete="email" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="Email address" value="{{ $email ?? old('email') }}">
            </div>
        </div>
        <div>
            <label for="password" class="sr-only">Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-lock"></i>
                </span>
                <input id="password" name="password" type="password" autocomplete="new-password" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="New Password">
            </div>
        </div>
        <div>
            <label for="password_confirmation" class="sr-only">Confirm Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-lock"></i>
                </span>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="Confirm New Password">
            </div>
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
