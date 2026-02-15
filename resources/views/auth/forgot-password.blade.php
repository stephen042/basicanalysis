@extends('layouts.guest')

@section('title', 'Forgot Your Password')

@section('content')
<div>
    <h2 class="mt-6 text-3xl font-extrabold text-center text-white">
        Reset your password
    </h2>
    <p class="mt-2 text-sm text-center text-gray-400">
        Enter your email address and we will send you a link to reset your password.
    </p>
</div>

<form class="mt-8 space-y-6" method="POST" action="{{ route('password.email') }}">
    @csrf

    @if (session('status'))
        <div class="p-4 text-sm text-green-300 bg-green-500 rounded-md bg-opacity-10">
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

    <div class="rounded-md shadow-sm">
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
        </div>
    </div>

    <div>
        <button type="submit"
            class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white border border-transparent rounded-md group bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
            Email Password Reset Link
        </button>
    </div>

    <div class="text-sm text-center">
        <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary-dark">
            Back to login
        </a>
    </div>

    <div class="text-sm text-center text-gray-400">
        &copy; Copyright {{ date('Y') }} {{ $settings->site_name }}. All Rights Reserved.
    </div>
form>
@endsection
