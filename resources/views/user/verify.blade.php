
@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="container px-4 py-10 mx-auto">
    <div class="max-w-3xl mx-auto">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-white md:text-4xl">KYC Verification</h1>
            <p class="mt-2 text-gray-400">To comply with regulations and ensure account security, we require identity verification.</p>
        </div>

        <x-danger-alert/>
        <x-success-alert/>

        <div class="p-8 rounded-lg shadow-lg bg-dark-200">
            @if (Auth::user()->account_verify == 'Verified')
                <div class="text-center">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-green-400 bg-green-500/10 rounded-full">
                        <i class="text-4xl fas fa-check-circle"></i>
                    </div>
                    <h2 class="mb-2 text-2xl font-bold text-white">You are Verified</h2>
                    <p class="text-gray-400">Your account has been successfully verified. You now have full access to all features on our platform.</p>
                    <a href="{{ route('dashboard') }}" class="inline-block px-6 py-3 mt-6 font-semibold text-white rounded-lg bg-primary hover:bg-primary-dark">
                        Go to Dashboard
                    </a>
                </div>
            @elseif (Auth::user()->account_verify == 'Under review')
                <div class="text-center">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-yellow-400 bg-yellow-500/10 rounded-full">
                        <i class="text-4xl fas fa-hourglass-half"></i>
                    </div>
                    <h2 class="mb-2 text-2xl font-bold text-white">Verification in Progress</h2>
                    <p class="max-w-md mx-auto text-gray-400">Your documents have been submitted and are currently under review. This process usually takes 1-2 business days. We'll notify you via email once it's complete.</p>
                    <button class="w-full px-6 py-3 mt-6 font-semibold text-gray-500 bg-gray-700 rounded-lg cursor-not-allowed" disabled>
                        Verification Pending
                    </button>
                </div>
            @else
                <div class="text-center">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-primary-400 bg-primary-500/10 rounded-full">
                        <i class="text-4xl fas fa-shield-alt"></i>
                    </div>
                    <h2 class="mb-2 text-2xl font-bold text-white">Verify Your Identity</h2>
                    <p class="max-w-md mx-auto text-gray-400">To unlock all features and secure your account, please complete our identity verification process. It's quick, easy, and keeps your investments safe.</p>
                    <a href="{{ route('kycform') }}" class="inline-block px-8 py-3 mt-6 font-semibold text-white rounded-lg bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-dark-200 focus:ring-primary">
                        Start KYC Verification
                    </a>
                </div>
            @endif
        </div>

        <div class="p-6 mt-8 text-center rounded-lg bg-dark-200">
            <h4 class="font-semibold text-white">Need help?</h4>
            <p class="mt-1 text-gray-400">If you have any questions or issues with the verification process, our support team is ready to assist you.</p>
            <a href="{{ route('support') }}" class="inline-block px-5 py-2 mt-4 text-sm font-medium text-white rounded-lg bg-dark-300 hover:bg-dark-100">
                Contact Support
            </a>
        </div>
    </div>
</div>
@endsection

