@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white md:text-3xl">Account Settings</h1>
        <p class="mt-1 text-sm text-gray-400">Manage your profile, security, and withdrawal settings.</p>
    </div>

    <x-danger-alert />
    <x-success-alert />
    <x-error-alert />

    <div x-data="{ tab: 'personal' }" class="flex flex-col gap-8 lg:flex-row">
        <!-- Sidebar Navigation -->
        <aside class="flex-shrink-0 lg:w-1/4">
            <div class="p-2 rounded-lg bg-dark-200">
                <nav class="space-y-1">
                    <button @click="tab = 'personal'"
                        :class="{ 'bg-primary-600 text-white': tab === 'personal', 'text-gray-400 hover:bg-dark-100 hover:text-white': tab !== 'personal' }"
                        class="flex items-center w-full gap-3 px-3 py-2 text-sm font-medium text-left transition-colors duration-200 rounded-md">
                        <i class="w-5 text-center fas fa-user-edit"></i>
                        Personal Information
                    </button>
                    <button @click="tab = 'withdrawal'"
                        :class="{ 'bg-primary-600 text-white': tab === 'withdrawal', 'text-gray-400 hover:bg-dark-100 hover:text-white': tab !== 'withdrawal' }"
                        class="flex items-center w-full gap-3 px-3 py-2 text-sm font-medium text-left transition-colors duration-200 rounded-md">
                        <i class="w-5 text-center fas fa-wallet"></i>
                        Withdrawal Settings
                    </button>
                    <button @click="tab = 'password'"
                        :class="{ 'bg-primary-600 text-white': tab === 'password', 'text-gray-400 hover:bg-dark-100 hover:text-white': tab !== 'password' }"
                        class="flex items-center w-full gap-3 px-3 py-2 text-sm font-medium text-left transition-colors duration-200 rounded-md">
                        <i class="w-5 text-center fas fa-key"></i>
                        Password
                    </button>
                    <button @click="tab = 'security'"
                        :class="{ 'bg-primary-600 text-white': tab === 'security', 'text-gray-400 hover:bg-dark-100 hover:text-white': tab !== 'security' }"
                        class="flex items-center w-full gap-3 px-3 py-2 text-sm font-medium text-left transition-colors duration-200 rounded-md">
                        <i class="w-5 text-center fas fa-shield-alt"></i>
                        Email Preferences
                    </button>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1">
            <div class="p-6 bg-dark-200 rounded-xl">
                <div x-show="tab === 'personal'" x-cloak>
                    <h2 class="text-xl font-bold text-white">Personal Information</h2>
                    <p class="mt-1 text-sm text-gray-400">Update your personal details here.</p>
                    <div class="mt-6">
                        @include('profile.update-profile-information-form')
                    </div>
                </div>
                <div x-show="tab === 'withdrawal'" x-cloak>
                    <h2 class="text-xl font-bold text-white">Withdrawal Settings</h2>
                    <p class="mt-1 text-sm text-gray-400">Update your bank and crypto wallet details for withdrawals.</p>
                    <div class="mt-6">
                        @include('profile.update-withdrawal-method')
                    </div>
                </div>
                <div x-show="tab === 'password'" x-cloak>
                    <h2 class="text-xl font-bold text-white">Change Password</h2>
                    <p class="mt-1 text-sm text-gray-400">Ensure your account is using a long, random password to stay secure.</p>
                    <div class="mt-6">
                        @include('profile.update-password-form')
                    </div>
                </div>
                <div x-show="tab === 'security'" x-cloak>
                    <h2 class="text-xl font-bold text-white">Email & Security Preferences</h2>
                    <p class="mt-1 text-sm text-gray-400">Manage your email notifications and security settings.</p>
                    <div class="mt-6">
                        @include('profile.update-security-form')
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
