@extends('layouts.guest')

@section('title', 'Verify Your Email Address')

@section('content')
<div>
    <h2 class="mt-6 text-3xl font-extrabold text-center text-white">
        Verify Your Email Address
    </h2>
    <p class="mt-2 text-sm text-center text-gray-400">
        Please check your inbox for a verification link.
    </p>
</div>

<div class="mt-8 space-y-6">
    @if (session('status') == 'verification-link-sent')
        <div class="p-4 text-sm text-green-300 bg-green-500 rounded-md bg-opacity-10">
            A new verification link has been sent to the email address you provided during registration.
        </div>
    @else
        <div class="p-4 text-sm text-blue-300 bg-blue-500 rounded-md bg-opacity-10">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </div>
    @endif

    <div class="flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white border border-transparent rounded-md group bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-gray-400 hover:text-white">
                Log Out
            </button>
        </form>
    </div>

    <div class="text-sm text-center text-gray-400">
        &copy; Copyright {{ date('Y') }} {{ $settings->site_name }}. All Rights Reserved.
    </div>
</div>
@endsection
