@php
      // Generate simple math CAPTCHA
      $num1 = rand(1, 10);
      $num2 = rand(1, 10);
      $captcha_question = "$num1 + $num2";
      $captcha_answer = $num1 + $num2;
@endphp
@extends('layouts.guest')

@section('title', 'Create an Account')

@section('content')
<div>
    <h2 class="mt-6 text-3xl font-extrabold text-center text-white">
        Create your account
    </h2>
    <p class="mt-2 text-sm text-center text-gray-400">
        Or
        <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary-dark">
            sign in to your existing account
        </a>
    </p>
</div>

<form class="mt-8 space-y-6" method="POST" action="{{ route('register') }}">
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

    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
        <div class="space-y-1">
            <label for="username" class="sr-only">Username</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-user"></i>
                </span>
                <input type="text" name="username" id="username" placeholder="Username" required value="{{ old('username') }}"
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
            </div>
        </div>

        <div class="space-y-1">
            <label for="name" class="sr-only">Full Name</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-user-check"></i>
                </span>
                <input type="text" name="name" value="{{ old('name') }}" id="name" placeholder="Full Name" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
            </div>
        </div>

        <div class="space-y-1">
            <label for="email" class="sr-only">Email Address</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-envelope"></i>
                </span>
                <input type="email" name="email" value="{{ old('email') }}" id="email" placeholder="Email Address" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
            </div>
        </div>

        <div class="space-y-1">
            <label for="phone" class="sr-only">Phone Number</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-phone"></i>
                </span>
                <input type="tel" name="phone" value="{{ old('phone') }}" id="phone" placeholder="Phone Number" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
            </div>
        </div>

        <div class="space-y-1">
            <label for="password" class="sr-only">Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-lock"></i>
                </span>
                <input type="password" name="password" id="password" placeholder="Password" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
            </div>
        </div>

        <div class="space-y-1">
            <label for="password_confirmation" class="sr-only">Confirm Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-lock"></i>
                </span>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" required
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
            </div>
        </div>

        <div class="space-y-1">
            <label for="currency" class="sr-only">Choose currency</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-dollar-sign"></i>
                </span>
                <select name="currency" id="currency" required
                    class="relative block w-full px-3 py-3 pl-10 text-white border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                    <option selected disabled>Choose currency</option>
                    <option value="$" selected>$ USD - US Dollar</option>
                    <option value="€">&euro; EUR - Euro</option>
                    <option value="£">&pound; GBP - British Pound</option>
                    <option value="¥">&yen; JPY - Japanese Yen</option>
                    <option value="¥">&yen; CNY - Chinese Yuan</option>
                    <option value="₹">&#8377; INR - Indian Rupee</option>
                    <option value="₽">&#8381; RUB - Russian Ruble</option>
                    <option value="R">R ZAR - South African Rand</option>
                    <option value="$">$ CAD - Canadian Dollar</option>
                    <option value="$">$ AUD - Australian Dollar</option>
                    <option value="CHF">CHF - Swiss Franc</option>
                    <option value="kr">kr SEK - Swedish Krona</option>
                    <option value="kr">kr NOK - Norwegian Krone</option>
                    <option value="kr">kr DKK - Danish Krone</option>
                    <option value="zł">z&#322; PLN - Polish Zloty</option>
                    <option value="₺">&#8378; TRY - Turkish Lira</option>
                    <option value="$">$ MXN - Mexican Peso</option>
                    <option value="R$">R$ BRL - Brazilian Real</option>
                    <option value="$">$ SGD - Singapore Dollar</option>
                    <option value="$">$ HKD - Hong Kong Dollar</option>
                    <option value="₩">&#8361; KRW - South Korean Won</option>
                    <option value="Rp">Rp IDR - Indonesian Rupiah</option>
                    <option value="₱">&#8369; PHP - Philippine Peso</option>
                    <option value="RM">RM MYR - Malaysian Ringgit</option>
                    <option value="฿">&#3647; THB - Thai Baht</option>
                    <option value="د.إ">&#1583;.&#1573; AED - UAE Dirham</option>
                    <option value="﷼">&#65020; SAR - Saudi Riyal</option>
                    <option value="₪">&#8362; ILS - Israeli Shekel</option>
                    <option value="KSh">KSh KES - Kenyan Shilling</option>
                </select>
            </div>
        </div>

        <div class="space-y-1">
            <label for="country" class="sr-only">Country</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-map-pin"></i>
                </span>
                <select name="country" id="country" required
                    class="relative block w-full px-3 py-3 pl-10 text-white border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                    <option selected disabled>Choose Country</option>
                    @include('auth.countries')
                </select>
            </div>
        </div>

        @if (Session::has('ref_by'))
        <div class="sm:col-span-2">
            <label for="ref_by" class="sr-only">Referral ID</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-user-plus"></i>
                </span>
                <input type="text" name="ref_by" value="{{ session('ref_by') }}" readonly placeholder="Referral ID"
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
            </div>
        </div>
        @else
        <div class="sm:col-span-2">
            <label for="ref_by" class="sr-only">Referral ID (Optional)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="text-gray-400 fa-solid fa-user-plus"></i>
                </span>
                <input type="text" name="ref_by" placeholder="Referral ID (Optional)"
                    class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
            </div>
        </div>
        @endif

        <!-- Math Verification -->
        <div class="sm:col-span-2">
            <div class="space-y-1">
                <!-- Math Problem Display -->
                <div class="text-center p-4 rounded-md bg-dark-100 border border-dark-300 mb-3">
                    <p class="text-sm text-gray-400 mb-2">Please solve this simple math problem:</p>
                    <div class="text-2xl font-bold text-primary">
                        {{ $captcha_question }} = ?
                    </div>
                </div>

                <!-- Answer Input -->
                <label for="captcha" class="sr-only">Math Answer</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="text-gray-400 fa-solid fa-calculator"></i>
                    </span>
                    <input type="number" name="captcha" id="captcha" placeholder="Enter your answer" required
                        class="relative block w-full px-3 py-3 pl-10 text-white placeholder-gray-500 border rounded-md appearance-none bg-dark-100 border-dark-300 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                        min="0" max="99" autocomplete="off">
                </div>

                @error('captcha')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hidden CAPTCHA confirmation -->
            <input type="hidden" name="captcha_confirmation" value="{{ $captcha_answer }}">
        </div>

        @if ($settings->captcha == 'true')
        <div class="sm:col-span-2">
            <div class="{{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }}">
                <label class="sr-only">Captcha</label>
                <div class="flex justify-center">
                    {!! NoCaptcha::display(['data-theme' => 'dark']) !!}
                </div>
                @if ($errors->has('g-recaptcha-response'))
                <span class="mt-2 text-sm text-red-500 help-block">
                    <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="flex items-center">
        <input id="terms" name="terms" type="checkbox" required
            class="w-4 h-4 border-gray-500 rounded text-primary bg-dark-100 focus:ring-primary">
        <label for="terms" class="block ml-2 text-sm text-gray-300">
            I accept the <a href="{{ route('privacy') }}" class="font-medium text-primary hover:text-primary-dark">Terms and Condition</a>
        </label>
    </div>

    <div>
        <button type="submit"
            class="relative flex justify-center w-full px-4 py-3 text-sm font-medium text-white border border-transparent rounded-md group bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
            Create Account
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
