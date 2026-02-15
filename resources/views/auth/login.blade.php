@extends('layouts.guest')

@section('title', 'Account Login')

@section('content')
<div>
    <h2 class="mt-6 text-3xl font-extrabold text-center text-white">
        Sign in to your account
    </h2>
    <p class="mt-2 text-sm text-center text-gray-400">
        Or
        <a href="{{ route('register') }}" class="font-medium text-primary hover:text-primary-dark">
            create a new account
        </a>
    </p>
</div>

<form class="mt-8 space-y-6" method="POST" action="{{ route('login') }}">
    @csrf

    @if (Session::has('status'))
    <div class="p-4 text-sm text-red-300 bg-red-500 rounded-md bg-opacity-10">
        {{ session('status') }}
    </div>
    @endif

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
            <label for="email-address" class="sr-only">Email address or Username</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-envelope"></i>
                </span>
                <input id="email-address" name="email" type="text"  required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="Email address or Username" value="{{ old('email') }}">
            </div>
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

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input id="remember-me" name="remember" type="checkbox"
                class="w-4 h-4 border-gray-500 rounded text-primary bg-dark-100 focus:ring-primary">
            <label for="remember-me" class="block ml-2 text-sm text-gray-300">
                Remember me
            </label>
        </div>

        <div class="text-sm">
            <a href="{{ route('password.request') }}" class="font-medium text-primary hover:text-primary-dark">
                Forgot your password?
            </a>
        </div>
    </div>

    <div>
        <button type="submit"
            class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white border border-transparent rounded-md group bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
            Sign in
        </button>
    </div>

    @if ($settings->enable_social_login == 'yes')
    <div class="relative flex items-center justify-center">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-dark-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 text-gray-500 bg-dark">Or continue with</span>
        </div>
    </div>

    <div>
        <a href="{{ route('social.redirect', ['social' => 'google']) }}"
            class="inline-flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-gray-300 border rounded-md border-dark-300 bg-dark-100 hover:bg-dark-200">
            <i class="mr-3 fab fa-google"></i>
            Google
        </a>
    </div>
    @endif

    <div class="text-sm text-center text-gray-400">
        &copy; Copyright {{ date('Y') }} {{ $settings->site_name }}. All Rights Reserved.
    </div>
</form>
@endsection
