@inject('uc', 'App\Http\Controllers\User\UsersController')
@php
    $array = \App\Models\User::all();
    $usr = Auth::user()->id;
@endphp
@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Refer & Earn</h1>
            <p class="mt-1 text-gray-400">Invite friends to {{ $settings->site_name }} and earn rewards from their investments.</p>
        </div>

        <x-danger-alert />
        <x-success-alert />

        <div class="p-6 rounded-lg bg-dark-200">
            <h2 class="text-xl font-bold text-white">Your Referral Link</h2>
            <p class="mt-1 text-sm text-gray-400">Share this link with your friends. When they sign up, you'll be their referrer.</p>

            <div x-data="{
                link: '{{ Auth::user()->ref_link }}',
                copied: false,
                copyToClipboard() {
                    navigator.clipboard.writeText(this.link).then(() => {
                        this.copied = true;
                        setTimeout(() => { this.copied = false }, 2500);
                    });
                }
            }" class="flex items-center mt-4 space-x-2">
                <input type="text" :value="link" readonly class="w-full px-4 py-3 text-gray-300 border-transparent rounded-lg bg-dark-300 focus:ring-primary-500 focus:outline-none">
                <button @click="copyToClipboard" class="flex items-center justify-center px-4 py-3 font-semibold text-white transition-colors duration-200 rounded-lg w-36 bg-primary-600 hover:bg-primary-700">
                    <span x-show="!copied" class="flex items-center">
                        <i class="mr-2 text-lg far fa-copy"></i> Copy
                    </span>
                    <span x-show="copied" x-cloak class="flex items-center text-white">
                        <i class="mr-2 text-lg fas fa-check"></i> Copied!
                    </span>
                </button>
            </div>

            <div class="mt-6">
                <p class="text-sm text-gray-400">Or share via social media:</p>
                <div class="flex items-center mt-2 space-x-2">
                    @php
                        $refLink = urlencode(Auth::user()->ref_link);
                        $refText = urlencode("Join me on " . $settings->site_name . " and start investing today! Use my link to sign up: ");
                    @endphp
                    <a href="https://twitter.com/intent/tweet?text={{ $refText . $refLink }}" target="_blank" class="flex items-center justify-center w-10 h-10 text-xl text-white transition-colors duration-200 bg-gray-700 rounded-full hover:bg-blue-500">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $refLink }}" target="_blank" class="flex items-center justify-center w-10 h-10 text-xl text-white transition-colors duration-200 bg-gray-700 rounded-full hover:bg-blue-700">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://wa.me/?text={{ $refText . $refLink }}" target="_blank" class="flex items-center justify-center w-10 h-10 text-xl text-white transition-colors duration-200 bg-gray-700 rounded-full hover:bg-green-500">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://t.me/share/url?url={{ $refLink }}&text={{ $refText }}" target="_blank" class="flex items-center justify-center w-10 h-10 text-xl text-white transition-colors duration-200 bg-gray-700 rounded-full hover:bg-blue-400">
                        <i class="fab fa-telegram-plane"></i>
                    </a>
                </div>
            </div>

            <div class="pt-6 mt-6 border-t border-dark-300">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-400">Your Referral ID</p>
                        <p class="text-lg font-semibold text-white">{{ Auth::user()->username }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">You were referred by</p>
                        <p class="text-lg font-semibold text-white">{{ $uc->getUserParent($usr) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-lg bg-dark-200">
            <h2 class="text-xl font-bold text-white">Your Referrals</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-dark-300">
                    <thead class="bg-dark-300">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Client Name</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Ref. Level</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Parent</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Status</th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-300 uppercase">Date Registered</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-dark-200 divide-dark-300">
                        {!! $uc->getdownlines($array, $usr) !!}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

