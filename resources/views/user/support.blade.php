
@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="container px-4 py-6 mx-auto">
    <div class="grid gap-8 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <div class="p-6 rounded-lg shadow-lg bg-dark-200">
                <h2 class="mb-4 text-2xl font-bold text-white">Send us a Message</h2>
                <p class="mb-6 text-gray-400">Have a question, comment, or suggestion? Drop us a line below. We'll get back to you as soon as possible.</p>

                <x-danger-alert/>
                <x-success-alert/>

                <form method="post" action="{{route('enquiry')}}">
                    @csrf
                    <input type="hidden" name="name" value="{{Auth::user()->name}}" />
                    <input type="hidden" name="email" value="{{Auth::user()->email}}">

                    <div class="mb-4">
                        <label for="subject" class="block mb-2 text-sm font-medium text-gray-300">Subject</label>
                        <input type="text" name="subject" id="subject" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                    </div>

                    <div class="mb-6">
                        <label for="message" class="block mb-2 text-sm font-medium text-gray-300">Message</label>
                        <textarea name="message" id="message" rows="6" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500" required></textarea>
                    </div>

                    <div>
                        <button type="submit" class="w-full px-6 py-3 font-semibold text-white rounded-lg bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-dark-200 focus:ring-primary">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="lg:col-span-5">
            <div class="p-6 rounded-lg shadow-lg bg-dark-200">
                <h3 class="mb-4 text-xl font-bold text-white">Contact Information</h3>
                <p class="mb-6 text-gray-400">You can also reach us through the following channels.</p>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 mt-1">
                            <i class="text-2xl text-primary fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-white">Email Us</h4>
                            <p class="text-gray-400">For any inquiries, suggestions, or complaints.</p>
                            <a href="mailto:{{$settings->contact_email}}" class="text-primary hover:underline">{{$settings->contact_email}}</a>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 mt-1">
                            <i class="text-2xl text-primary fas fa-life-ring"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-white">24/7 Support</h4>
                            <p class="text-gray-400">Our team is available around the clock to assist you.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 mt-8 rounded-lg shadow-lg bg-dark-200" x-data="{ open: 'faq-1' }">
                <h3 class="mb-4 text-xl font-bold text-white">Frequently Asked Questions</h3>
                <div class="space-y-4">
                    <div>
                        <button @click="open = open === 'faq-1' ? '' : 'faq-1'" class="flex items-center justify-between w-full text-left">
                            <h4 class="font-semibold text-white">How do I make a deposit?</h4>
                            <i class="fas" :class="{'fa-chevron-down': open !== 'faq-1', 'fa-chevron-up': open === 'faq-1'}"></i>
                        </button>
                        <div x-show="open === 'faq-1'" x-collapse class="pt-2 mt-2 border-t border-dark-100">
                            <p class="text-gray-400">To make a deposit, navigate to the 'Deposit' section in your dashboard, choose your preferred payment method, and follow the on-screen instructions.</p>
                        </div>
                    </div>
                    <div>
                        <button @click="open = open === 'faq-2' ? '' : 'faq-2'" class="flex items-center justify-between w-full text-left">
                            <h4 class="font-semibold text-white">How can I withdraw my earnings?</h4>
                            <i class="fas" :class="{'fa-chevron-down': open !== 'faq-2', 'fa-chevron-up': open === 'faq-2'}"></i>
                        </button>
                        <div x-show="open === 'faq-2'" x-collapse class="pt-2 mt-2 border-t border-dark-100">
                            <p class="text-gray-400">You can request a withdrawal from the 'Withdrawal' page. Please ensure your payment details are up to date for a smooth transaction.</p>
                        </div>
                    </div>
                    <div>
                        <button @click="open = open === 'faq-3' ? '' : 'faq-3'" class="flex items-center justify-between w-full text-left">
                            <h4 class="font-semibold text-white">Is my investment secure?</h4>
                            <i class="fas" :class="{'fa-chevron-down': open !== 'faq-3', 'fa-chevron-up': open === 'faq-3'}"></i>
                        </button>
                        <div x-show="open === 'faq-3'" x-collapse class="pt-2 mt-2 border-t border-dark-100">
                            <p class="text-gray-400">Yes, we use state-of-the-art security measures, including encryption and two-factor authentication, to protect your account and investments.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

