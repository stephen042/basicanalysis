@extends('layouts.base')

@section('title', 'Terms')



@section('content')


<!-- Privacy & Policy Section -->
    <section class="py-16 lg:py-24" style="margin-top: 130px;">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="bg-gray-800 rounded-2xl p-8 lg:p-12">
                <!-- Header -->
                <div class="text-center mb-12 lg:mb-16">
                    <h1 class="text-3xl lg:text-4xl font-bold">Terms & Conditions</h1>
                </div>

                <div class="space-y-12 lg:space-y-16">
                    <!-- Introduction Section -->
                    <div class="space-y-6">
                        <p class="text-gray-300 leading-relaxed">
                            {{$settings->site_name}} website/platform is available in English Language. The English version is the original version and the only one binding on {{$settings->site_name}} it shall prevail on any other version in case of discrepancy. {{$settings->site_name}} is not responsible for any erroneous, inadequate, or misleading translations from the original version into other languages unless communicated to users. {{$settings->site_name}} allows access to its web page and users related services by any individual or corporate entity (herein after referred to as the Investor/Client) according to the procedure and on the terms and conditions described in this Agreement. The Agreement becomes effective as of the date the Investor opened an investment account and transferred funds to the Company's account(s) to ensure minimum investment deposit. In the Agreement any reference to an individual person includes corporate entities, unincorporated associations, partnerships and individuals. Headings and notes in the Agreement are for reference only and shall not affect the contents and interpretation of the Agreement.
                        </p>
                    </div>

                    <!-- Investors and Company's Responsibilities -->
                    <div class="space-y-6">
                        <h3 class="text-2xl font-bold text-cyan-400 border-b border-gray-700 pb-3">INVESTORS AND COMPANY'S RESPONSIBILITIES</h3>
                        
                        <ul class="content-list space-y-4 text-gray-300">
                            <li>
                                <p>Investors are notified and agrees that the Company cannot be held liable for Investor's actions during automated bot trading operations. Responsibility for bot configuration, investment monitoring and trading account oversight is the Investor's duty.</p>
                            </li>
                            <li>
                                <p>Company reserves the right to amend this Agreement thereby agreeing to send a notice to Investors via E-mail about its amendment at least 2 business days prior to the effective date of these amendments.</p>
                            </li>
                            <li>
                                <p>All rights and obligations of the Company and Investors represents a long-term commitment, which remains in force up until the Company receives an Investor's notice of termination of this agreement or closing his {{$settings->site_name}} automated bot trading account.</p>
                            </li>
                            <li>
                                <p>Company would not be held responsible for non-fulfillment of any obligations involving quality of online communication of information to an Investor terminal or use of information, platform, software and interface of websites which do not belong to the Company.</p>
                            </li>
                            <li>
                                <p>Company would not be held responsible for non-fulfillment of any obligations involving quality of online communication of information to an Investor terminal or use of information, platform, software and interface of websites which do not belong to the Company.</p>
                            </li>
                            <li>
                                <p>Any bot trading recommendations, performance data, and information communicated to an Investor by {{$settings->site_name}} and its representatives do not constitute as an offer to make operation/transaction or guarantee of bot performance.</p>
                            </li>
                        </ul>
                    </div>

                    <!-- User Representations -->
                    <div class="space-y-6">
                        <h3 class="text-2xl font-bold text-cyan-400 border-b border-gray-700 pb-3">User Representations</h3>
                        
                        <p class="text-gray-300 leading-relaxed">
                            We use cookies and similar tracking technologies to enhance your experience on our automated bot trading platform and gather information about your interactions with our AI-powered trading services.
                        </p>
                        
                        <ol class="decimal-list space-y-4 text-gray-300 mt-6">
                            <li>
                                <span class="font-medium">Acceptance:</span> Users are typically required to agree to the terms and conditions before using our automated bot trading service, by clicking an acceptance button or by activating any trading bot.
                            </li>
                            <li>
                                <span class="font-medium">User rights and responsibilities:</span> The terms and conditions specify the rights granted to users and the responsibilities they have while using our automated bot trading service, including bot configuration and risk management.
                            </li>
                            <li>
                                <span class="font-medium">Content guidelines:</span> Your reviews should not contain offensive profanity, racist, offensive, or hate language.
                            </li>
                            <li>
                                <span class="font-medium">Dispute resolution:</span> Procedures for resolving disputes related to bot trading performance, account issues, or service quality, such as arbitration or mediation, may be outlined in the terms and conditions.
                            </li>
                            <li>
                                <span class="font-medium">Intellectual property:</span> These sections outline the ownership and usage rights of intellectual property such as copyrights, trademarks, and patents associated with our AI bot algorithms, trading software, and automated trading services.
                            </li>
                        </ol>
                        
                        <p class="text-gray-300 leading-relaxed mt-6">
                            Remember that it's important to read and understand the specific terms and conditions of any service or product you use. If you have any questions or concerns about a particular set of terms clarification.
                        </p>
                    </div>

                    <!-- Guideline for Reviews -->
                    <div class="space-y-6">
                        <h3 class="text-2xl font-bold text-cyan-400 border-b border-gray-700 pb-3">Guideline for Reviews</h3>
                        
                        <ol class="decimal-list space-y-4 text-gray-300">
                            <li>You should have firsthand experience with the bot trading service being reviewed.</li>
                            <li>Your reviews should not contain offensive profanity, offensive, or hate language.</li>
                            <li>Your reviews should not contain discriminatory references based on religion, race, gender, national origin, age, marital status, sexual orientation, or disability.</li>
                            <li>Your reviews should not contain references to illegal activity.</li>
                            <li>You may not organize encouraging others to post reviews, whether positive or negative.</li>
                        </ol>
                        
                        <p class="text-gray-300 leading-relaxed mt-6">
                            We may accept, reject, or remove reviews at our sole discretion. We have absolutely no obligation to screen reviews or to delete reviews, even if anyone considers reviews objectionable or inaccurate.
                        </p>
                    </div>

                    <!-- Bot Trading Risks -->
                    <div class="space-y-6">
                        <h3 class="text-2xl font-bold text-cyan-400 border-b border-gray-700 pb-3">Bot Trading Risks</h3>
                        
                        <p class="text-gray-300 leading-relaxed">
                            Users acknowledge that automated bot trading involves inherent risks including market volatility, potential losses, and technical failures. While our AI-powered bots use advanced algorithms and risk management features, past performance does not guarantee future results. Users should only invest funds they can afford to lose.
                        </p>
                        
                        <p class="text-gray-300 leading-relaxed mt-4">
                            You represent and warrant that you understand the risks associated with automated bot trading and have the financial capacity to sustain potential losses. The Company is not liable for any trading losses incurred through the use of our automated bot trading services, as all trading decisions are executed by algorithms based on user-defined parameters and market conditions.
                        </p>
                    </div>
                </div>

                <!-- Acceptance Footer -->
                <div class="mt-12 pt-8 border-t border-gray-700">
                    <div class="bg-gray-700/50 rounded-xl p-6 text-center">
                        <p class="text-gray-300">
                            By using our automated bot trading platform, you acknowledge that you have read, understood, and agree to be bound by these Terms & Conditions.
                        </p>
                        <p class="text-sm text-gray-400 mt-2">
                            Last updated: {{ date('F j, Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Additional Legal Notice -->
    <section class="py-12 bg-gray-800">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="text-center">
                <h3 class="text-xl font-bold text-cyan-400 mb-4">Legal Notice</h3>
                <p class="text-gray-400 max-w-2xl mx-auto">
                    This document constitutes a legal agreement between you and {{$settings->site_name}}. 
                    Please consult with legal counsel if you have any questions about these terms.
                </p>
            </div>
        </div>
    </section>


  

 
  
    
 
@endsection