@extends('layouts.dash')

@section('title', $title)

@section('content')
    <div class="space-y-6" id="compound-calculator">

        <!-- ============================== HEADER ============================== -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary-100 text-primary">
                        <i class="fas fa-bolt mr-2"></i> FREE TOOL · NO SIGNUP
                    </span>
                    <h1 class="mt-3 text-2xl md:text-3xl font-bold text-white">Compound Interest Calculator</h1>
                    <p class="mt-2 text-gray-400 text-sm md:text-base leading-relaxed">
                        Punch in a starting amount, your return, and how long you let it run. Add recurring
                        contributions if you keep funding the account — see the full period-by-period curve, the
                        effective annual rate, and a compound-vs-simple comparison.
                    </p>
                </div>
                <div class="hidden md:flex w-14 h-14 rounded-xl bg-primary/10 items-center justify-center shrink-0">
                    <i class="fas fa-calculator text-primary text-2xl"></i>
                </div>
                <a href="{{ route('compound-investment.create') }}"
                    class="inline-flex shrink-0 items-center gap-2 h-11 px-6 rounded-full font-bold bg-primary hover:bg-primary-dark text-white transition-colors">
                    Start Compounding <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- ============================ SUMMARY STATS ============================ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-dark-200 rounded-xl p-5 border border-dark-100">
                <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold">
                    Projected final balance · <span id="stat-horizon">12 MO</span>
                </p>
                <p class="mt-2 text-3xl font-black text-white tabular-nums" id="stat-final">$93,149</p>
                <p class="mt-1 text-sm text-green-400" id="stat-interest">+$88,149 interest earned</p>
                <p class="mt-1 text-xs text-gray-500" id="stat-withdrawn" hidden></p>
            </div>

            <div class="bg-dark-200 rounded-xl p-5 border border-dark-100">
                <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold">Total growth</p>
                <p class="mt-2 text-3xl font-black text-primary tabular-nums" id="stat-growth">+1763%</p>
                <p class="mt-1 text-sm text-gray-400" id="stat-multiple">18.63×</p>
            </div>

            <div class="bg-dark-200 rounded-xl p-5 border border-dark-100">
                <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold">Effective APY</p>
                <p class="mt-2 text-3xl font-black text-white tabular-nums" id="stat-apy">1763%</p>
                <p class="mt-1 text-sm text-gray-400">per year</p>
            </div>

            <div class="bg-dark-200 rounded-xl p-5 border border-dark-100">
                <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold">You put in</p>
                <p class="mt-2 text-3xl font-black text-white tabular-nums" id="stat-putin">$5,000</p>
                <p class="mt-1 text-sm text-gray-400" id="stat-putin-sub">principal</p>
            </div>
        </div>

        <!-- ============================ RATE BASIS + NOTE ============================ -->
        <div class="bg-dark-200 rounded-xl p-4 border border-dark-100 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="inline-flex rounded-full bg-dark-100 p-1 self-start">
                <button type="button" data-basis="month" class="basis-btn px-4 py-1.5 rounded-full text-xs font-bold transition">Per month</button>
                <button type="button" data-basis="year" class="basis-btn px-4 py-1.5 rounded-full text-xs font-bold transition">Per year</button>
            </div>
            <p class="text-sm text-gray-300" id="rate-note">Your 27.6% monthly return = 1762.98% effective APY</p>
        </div>

        <!-- ============================ YOUR SCENARIO ============================ -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-white">Your scenario</h2>
                <p class="text-sm text-gray-400">— tap any value to edit</p>
            </div>

            <!-- Editable tiles -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Starting amount -->
                <div class="rounded-2xl border border-dark-100 bg-dark-100 p-3.5 min-w-0 focus-within:border-primary transition-colors">
                    <label for="f-start" class="block text-[10px] uppercase tracking-widest text-gray-400 font-semibold">Starting amount</label>
                    <div class="flex items-baseline gap-1 mt-1.5">
                        <span class="text-gray-400 text-lg font-bold shrink-0">{{ $settings->currency ?? '$' }}</span>
                        <input id="f-start" type="text" inputmode="decimal" value="5000"
                            class="w-full bg-transparent text-2xl font-black tabular-nums text-white outline-none focus:text-primary">
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">What you invest today</p>
                    <p class="text-[10px] text-red-400 mt-1" id="err-start" hidden></p>
                </div>

                <!-- Interest rate -->
                <div class="rounded-2xl border border-dark-100 bg-dark-100 p-3.5 min-w-0 focus-within:border-primary transition-colors">
                    <label for="f-rate" class="block text-[10px] uppercase tracking-widest text-gray-400 font-semibold" id="rate-label">Monthly return</label>
                    <div class="flex items-baseline gap-1 mt-1.5">
                        <input id="f-rate" type="text" inputmode="decimal" value="27.6"
                            class="w-full bg-transparent text-2xl font-black tabular-nums text-primary outline-none">
                        <span class="text-gray-400 text-sm font-semibold shrink-0">%</span>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Example avg ≈ 15.4%/mo</p>
                    <p class="text-[10px] text-red-400 mt-1" id="err-rate" hidden></p>
                </div>

                <!-- Time horizon -->
                <div class="rounded-2xl border border-dark-100 bg-dark-100 p-3.5 min-w-0 focus-within:border-primary transition-colors">
                    <label for="f-horizon" class="block text-[10px] uppercase tracking-widest text-gray-400 font-semibold">Time horizon</label>
                    <div class="flex items-center gap-2 mt-1.5">
                        <input id="f-horizon" type="text" inputmode="numeric" value="12"
                            class="w-full min-w-0 bg-transparent text-2xl font-black tabular-nums text-white outline-none focus:text-primary">
                        <select id="f-unit" aria-label="Time unit"
                            class="shrink-0 bg-dark-200 border border-dark-100 rounded-lg px-2 py-1.5 text-xs font-semibold text-white focus:outline-none focus:border-primary">
                            <option value="mo">Months (mo)</option>
                            <option value="wk">Weeks (wk)</option>
                            <option value="day">Days (day)</option>
                        </select>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1" id="horizon-help">How long it runs (12 mo = 1 year)</p>
                    <p class="text-[10px] text-red-400 mt-1" id="err-horizon" hidden></p>
                </div>

                <!-- Recurring contribution -->
                <div class="rounded-2xl border border-dark-100 bg-dark-100 p-3.5 min-w-0 focus-within:border-primary transition-colors">
                    <label for="f-contribution" class="block text-[10px] uppercase tracking-widest text-gray-400 font-semibold" id="contribution-label">Adding each month</label>
                    <div class="flex items-baseline gap-1 mt-1.5">
                        <span class="text-gray-400 text-lg font-bold shrink-0">{{ $settings->currency ?? '$' }}</span>
                        <input id="f-contribution" type="text" inputmode="decimal" value="0"
                            class="w-full bg-transparent text-2xl font-black tabular-nums text-white outline-none focus:text-primary">
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Optional recurring top-up</p>
                    <p class="text-[10px] text-red-400 mt-1" id="err-contribution" hidden></p>
                </div>
            </div>

            <!-- Quick start -->
            <div class="mt-5 flex flex-col md:flex-row md:items-center gap-4">
                <div>
                    <p class="text-xs text-gray-400 mb-2">Quick start:</p>
                    <div class="flex flex-wrap gap-2" id="quick-start">
                        <button type="button" data-amount="500" class="quick-btn px-3 py-1 rounded-full text-xs font-semibold border border-dark-100 text-gray-300 hover:border-primary transition">$500</button>
                        <button type="button" data-amount="1000" class="quick-btn px-3 py-1 rounded-full text-xs font-semibold border border-dark-100 text-gray-300 hover:border-primary transition">$1,000</button>
                        <button type="button" data-amount="5000" class="quick-btn px-3 py-1 rounded-full text-xs font-semibold border border-dark-100 text-gray-300 hover:border-primary transition">$5,000</button>
                        <button type="button" data-amount="10000" class="quick-btn px-3 py-1 rounded-full text-xs font-semibold border border-dark-100 text-gray-300 hover:border-primary transition">$10,000</button>
                        <button type="button" data-amount="25000" class="quick-btn px-3 py-1 rounded-full text-xs font-semibold border border-dark-100 text-gray-300 hover:border-primary transition">$25,000</button>
                    </div>
                </div>

                <div class="md:ml-auto">
                    <label for="f-compounding" class="block text-xs text-gray-400 mb-2">Compounding frequency</label>
                    <select id="f-compounding"
                        class="w-full md:w-auto bg-dark-100 border border-dark-100 rounded-lg px-3 py-2 text-sm font-semibold text-white focus:outline-none focus:border-primary">
                        <option value="daily">Daily (365×/year)</option>
                        <option value="weekly">Weekly (52×/year)</option>
                        <option value="monthly" selected>Monthly (12×/year)</option>
                        <option value="quarterly">Quarterly (4×/year)</option>
                        <option value="annually">Annually (1×/year)</option>
                    </select>
                </div>
            </div>

            <!-- Profit withdrawal -->
            <div class="mt-6 pt-5 border-t border-dark-100">
                <p class="text-sm font-semibold text-white mb-1" id="withdraw-title">Take profits out each month?</p>
                <p class="text-xs text-gray-400 mb-3">
                    Keep everything in to compound, or take a share of each period's profit as cash and let the rest
                    keep growing.
                </p>
                <div class="flex flex-wrap gap-2" id="withdraw-options">
                    <button type="button" data-withdraw="0" class="withdraw-btn px-4 py-2 rounded-full text-xs font-bold border border-dark-100 transition">Keep it all in</button>
                    <button type="button" data-withdraw="25" class="withdraw-btn px-4 py-2 rounded-full text-xs font-bold border border-dark-100 transition">Take 25%</button>
                    <button type="button" data-withdraw="50" class="withdraw-btn px-4 py-2 rounded-full text-xs font-bold border border-dark-100 transition">Take 50%</button>
                    <button type="button" data-withdraw="75" class="withdraw-btn px-4 py-2 rounded-full text-xs font-bold border border-dark-100 transition">Take 75%</button>
                    <button type="button" data-withdraw="100" class="withdraw-btn px-4 py-2 rounded-full text-xs font-bold border border-dark-100 transition">Take all profit</button>
                </div>
            </div>
        </div>

        <!-- ============================== CHART ============================== -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-semibold text-white">Projected growth</h2>
                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm inline-block" style="background:rgba(245,192,109,.6)"></span>
                        <span id="chart-gain-label">This month's gain</span></span>
                    <span class="flex items-center gap-2"><span class="w-3 h-0.5 inline-block" style="background:rgb(21,149,125)"></span> Running balance</span>
                </div>
            </div>
            <div class="relative w-full" style="height: 320px;">
                <canvas id="growthChart"></canvas>
            </div>
            <div class="mt-4 rounded-lg bg-dark-100 border border-dark-100 p-4 text-sm text-gray-300" id="advantage-note">
                Compounding adds <span class="text-primary font-semibold" id="advantage-amount">$71,589</span> vs flat
                simple interest at the same rate.
                <span class="block mt-1 text-xs text-gray-500" id="simple-note">simple: $21,560</span>
            </div>
        </div>

        <!-- ========================= PROJECTION TABLE ========================= -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-white" id="projection-title">Month-by-month projection</h2>
                    <p class="text-sm text-gray-400">The same compounding curve, laid out so you can scan every period.</p>
                </div>
                <span class="inline-flex items-center self-start px-3 py-1 rounded-full text-xs font-semibold bg-dark-100 text-gray-300"
                    id="projection-subtitle">12 months · 27.6% / mo</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-dark-300">
                        <thead class="bg-dark-300">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-300 uppercase" id="col-period-1">Month</th>
                                <th scope="col" class="px-4 py-2 text-xs font-medium tracking-wider text-right text-gray-300 uppercase">Balance</th>
                                <th scope="col" class="px-4 py-2 text-xs font-medium tracking-wider text-right text-gray-300 uppercase">Principal</th>
                                <th scope="col" class="px-4 py-2 text-xs font-medium tracking-wider text-right text-gray-300 uppercase">Interest</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-300" id="projection-body-1"></tbody>
                    </table>
                </div>
                <div class="overflow-x-auto" id="projection-wrap-2">
                    <table class="min-w-full divide-y divide-dark-300">
                        <thead class="bg-dark-300">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-300 uppercase" id="col-period-2">Month</th>
                                <th scope="col" class="px-4 py-2 text-xs font-medium tracking-wider text-right text-gray-300 uppercase">Balance</th>
                                <th scope="col" class="px-4 py-2 text-xs font-medium tracking-wider text-right text-gray-300 uppercase">Principal</th>
                                <th scope="col" class="px-4 py-2 text-xs font-medium tracking-wider text-right text-gray-300 uppercase">Interest</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-300" id="projection-body-2"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ======================== CALCULATION BREAKDOWN ======================== -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h2 class="text-lg font-semibold text-white mb-4">Detailed calculation breakdown</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Starting amount</dt>
                    <dd class="text-white font-semibold tabular-nums" id="bd-start">$5,000.00</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Interest rate</dt>
                    <dd class="text-white font-semibold tabular-nums" id="bd-rate">27.6% per month</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Compounding</dt>
                    <dd class="text-white font-semibold tabular-nums" id="bd-compounding">Monthly · 27.6% per period</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Time horizon</dt>
                    <dd class="text-white font-semibold tabular-nums" id="bd-horizon">12 months = 1 year</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Recurring contribution</dt>
                    <dd class="text-white font-semibold tabular-nums" id="bd-contribution">None</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Contribution timing</dt>
                    <dd class="text-white font-semibold" id="bd-timing">End of each period (compounds next period)</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Profit withdrawal</dt>
                    <dd class="text-white font-semibold" id="bd-withdraw">Keep it all in</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Total contributions</dt>
                    <dd class="text-white font-semibold tabular-nums" id="bd-contributions">$0.00</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Total interest earned</dt>
                    <dd class="text-primary font-semibold tabular-nums" id="bd-interest">$88,148.98</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-dark-100 pb-2">
                    <dt class="text-gray-400">Final projected balance</dt>
                    <dd class="text-primary font-semibold tabular-nums" id="bd-final">$93,148.98</dd>
                </div>
            </dl>
            <p class="mt-4 text-xs text-gray-500 leading-relaxed" id="bd-formula">
                Balance is grown period by period: balance = balance × (1 + periodic rate). A recurring contribution is
                added at the end of every period and only starts earning from the following period.
            </p>
        </div>

        <!-- ============================== INFO CARDS ============================== -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
                    <i class="fas fa-snowball text-primary"></i>
                </div>
                <h3 class="text-base font-semibold text-white mb-2">What compounding does</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    Each period's gain is added to your balance, so the next period earns a return on a bigger base.
                    That snowball is why the curve bends upward instead of rising in a straight line.
                </p>
            </div>
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center mb-3">
                    <i class="fas fa-balance-scale text-blue-400"></i>
                </div>
                <h3 class="text-base font-semibold text-white mb-2">Compound vs simple</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    Simple interest pays the same flat amount every period on your original deposit. Compounding
                    reinvests every gain — the gap between the two grows wider the longer you stay invested.
                </p>
            </div>
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <div class="w-10 h-10 rounded-lg bg-yellow-500/10 flex items-center justify-center mb-3">
                    <i class="fas fa-plus-circle text-yellow-400"></i>
                </div>
                <h3 class="text-base font-semibold text-white mb-2" id="info-contribution-title">Adding each month</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    Recurring contributions stack new principal on top of a compounding balance. Even small top-ups
                    meaningfully lift the final number over time.
                </p>
            </div>
        </div>

        <!-- ================================ CTA ================================ -->
        <div class="bg-dark-200 rounded-xl p-6 md:p-8 border border-dark-100 text-center">
            <h2 class="text-xl md:text-2xl font-bold text-white">Ready to put compounding to work?</h2>
            <p class="mt-2 text-sm md:text-base text-gray-400 max-w-2xl mx-auto">
                Set up a compound investment with your chosen duration and recurring contribution schedule.
            </p>
            <a href="{{ route('compound-investment.create') }}"
                class="mt-4 inline-flex items-center gap-2 h-11 px-7 rounded-full font-bold bg-primary hover:bg-primary-dark text-white transition-colors">
                Start Compounding <i class="fas fa-arrow-right"></i>
            </a>
            <p class="mt-5 text-xs text-gray-500 max-w-3xl mx-auto leading-relaxed">
                This calculator is for illustration only. Projections assume a constant return and do not guarantee
                future results. Past performance is not indicative of future returns. Trading involves risk.
            </p>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/compound-interest-calculator.js') }}"></script>
    <script>
        (function () {
            'use strict';

            var engine = window.CompoundEngine;
            var CURRENCY = @json($settings->currency ?? '$');

            // Default scenario (matches the reference calculator example).
            var state = {
                start: '5000',
                rate: '27.6',
                rate_basis: 'month',
                unit: 'mo',
                horizon: '12',
                contribution: '0',
                compounding: 'monthly',
                withdraw_pct: '0'
            };

            var el = function (id) { return document.getElementById(id); };

            var COMPOUNDING_LABEL = {
                daily: 'Daily', weekly: 'Weekly', monthly: 'Monthly',
                quarterly: 'Quarterly', annually: 'Annually'
            };

            function money(value, digits) {
                digits = typeof digits === 'number' ? digits : 0;
                var n = Number(value);
                if (!isFinite(n)) n = 0;
                return CURRENCY + n.toLocaleString('en-US', {
                    minimumFractionDigits: digits,
                    maximumFractionDigits: digits
                });
            }

            function signedMoney(value, digits) {
                var n = Number(value);
                return (n >= 0 ? '+' : '-') + money(Math.abs(n), digits);
            }

            function axisMoney(value) {
                var n = Number(value);
                if (Math.abs(n) >= 1000000) return CURRENCY + (n / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
                if (Math.abs(n) >= 1000) return CURRENCY + (n / 1000) + 'k';
                return CURRENCY + n;
            }

            function pct(value, digits) {
                digits = typeof digits === 'number' ? digits : 0;
                var n = Number(value);
                if (!isFinite(n)) n = 0;
                return n.toLocaleString('en-US', {
                    minimumFractionDigits: digits,
                    maximumFractionDigits: digits,
                    useGrouping: false
                }) + '%';
            }

            function plural(name, count) {
                return count === 1 ? name : name + 's';
            }

            function showError(key, message) {
                var node = el('err-' + key);
                if (!node) return;
                if (message) {
                    node.textContent = message;
                    node.hidden = false;
                } else {
                    node.textContent = '';
                    node.hidden = true;
                }
            }

            // ------------------------------ chart ------------------------------
            var chart = null;

            function buildChart(labels, gains, balances) {
                var ctx = el('growthChart').getContext('2d');
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Gain',
                                data: gains,
                                backgroundColor: 'rgba(245, 192, 109, 0.55)',
                                borderColor: 'rgba(245, 192, 109, 0.9)',
                                borderWidth: 1,
                                order: 2
                            },
                            {
                                type: 'line',
                                label: 'Running balance',
                                data: balances,
                                borderColor: 'rgb(21, 149, 125)',
                                backgroundColor: 'rgba(21, 149, 125, 0.12)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: false,
                                pointRadius: labels.length > 60 ? 0 : 2,
                                pointBackgroundColor: 'rgb(21, 149, 125)',
                                order: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: labels.length > 120 ? false : { duration: 300 },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return context.dataset.label + ': ' + money(context.parsed.y, 2);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#9ca3af', maxRotation: 0, autoSkip: true, maxTicksLimit: 16 },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#9ca3af', callback: function (value) { return axisMoney(value); } },
                                grid: { color: 'rgba(255,255,255,0.06)' }
                            }
                        }
                    }
                });
            }

            function updateChart(labels, gains, balances) {
                if (!chart) {
                    buildChart(labels, gains, balances);
                    return;
                }
                chart.data.labels = labels;
                chart.data.datasets[0].data = gains;
                chart.data.datasets[1].data = balances;
                chart.data.datasets[1].pointRadius = labels.length > 60 ? 0 : 2;
                chart.update(chart.data.labels.length > 120 ? 'none' : undefined);
            }

            // ------------------------------ tables ------------------------------
            function rowHtml(row) {
                var interest = row.interest;
                var interestClass = interest > 0 ? 'text-green-400' : (interest < 0 ? 'text-red-400' : 'text-gray-400');
                return '<tr class="hover:bg-dark-100">' +
                    '<td class="px-4 py-2 text-sm text-white whitespace-nowrap">' + row.label + '</td>' +
                    '<td class="px-4 py-2 text-sm text-white text-right tabular-nums whitespace-nowrap">' + money(row.balance, 0) + '</td>' +
                    '<td class="px-4 py-2 text-sm text-gray-300 text-right tabular-nums whitespace-nowrap">' + money(row.principal, 0) + '</td>' +
                    '<td class="px-4 py-2 text-sm text-right tabular-nums whitespace-nowrap ' + interestClass + '">' + signedMoney(interest, 0) + '</td>' +
                    '</tr>';
            }

            function renderTable(series) {
                var half = Math.ceil(series.length / 2);
                var first = series.slice(0, half);
                var second = series.slice(half);

                el('projection-body-1').innerHTML = first.map(rowHtml).join('');
                el('projection-body-2').innerHTML = second.map(rowHtml).join('');
                el('projection-wrap-2').hidden = second.length === 0;
            }

            // ------------------------------ render ------------------------------
            function render() {
                var result = engine.calculate(state);
                var summary = result.summary;
                var inputs = result.inputs;
                var series = result.series;
                var unitName = engine.UNITS[inputs.unit].name;
                var unitPlural = plural(unitName, inputs.horizon);
                var unitAbbr = inputs.unit.toUpperCase();
                var basisWord = inputs.rate_basis === 'month' ? 'monthly' : 'annual';

                // field level validation messages
                ['start', 'rate', 'contribution', 'horizon'].forEach(function (key) {
                    showError(key, result.errors[key] || '');
                });

                // ---- summary cards ----
                el('stat-horizon').textContent = inputs.horizon + ' ' + unitAbbr;
                el('stat-final').textContent = money(summary.final_balance, 0);
                el('stat-interest').textContent = signedMoney(summary.interest_earned, 0) + ' interest earned';

                var withdrawnNode = el('stat-withdrawn');
                if (summary.withdrawn > 0) {
                    withdrawnNode.hidden = false;
                    withdrawnNode.textContent = money(summary.withdrawn, 0) + ' taken out as cash';
                } else {
                    withdrawnNode.hidden = true;
                }

                el('stat-growth').textContent = (summary.growth_pct >= 0 ? '+' : '') + pct(summary.growth_pct, 0);
                el('stat-multiple').textContent = summary.multiple.toLocaleString('en-US', {
                    minimumFractionDigits: 2, maximumFractionDigits: 2
                }) + '×';
                el('stat-apy').textContent = pct(summary.effective_apy, 0);
                el('stat-putin').textContent = money(summary.put_in, 0);
                el('stat-putin-sub').textContent = summary.total_contributions > 0
                    ? money(inputs.start, 0) + ' principal + ' + money(summary.total_contributions, 0) + ' added'
                    : 'principal';

                // ---- rate note + toggle ----
                el('rate-note').textContent = 'Your ' + pct(inputs.rate, inputs.rate % 1 === 0 ? 0 : 1) + ' ' + basisWord +
                    ' return = ' + pct(summary.effective_apy, summary.effective_apy % 1 === 0 ? 0 : 2) + ' effective APY';
                el('rate-label').textContent = inputs.rate_basis === 'month' ? 'Monthly return' : 'Annual return';

                document.querySelectorAll('.basis-btn').forEach(function (btn) {
                    var active = btn.getAttribute('data-basis') === inputs.rate_basis;
                    btn.className = 'basis-btn px-4 py-1.5 rounded-full text-xs font-bold transition ' +
                        (active
                            ? 'bg-primary text-white'
                            : 'text-gray-400 hover:text-white');
                });

                // ---- scenario tiles ----
                el('contribution-label').textContent = 'Adding each ' + unitName;
                el('horizon-help').textContent = 'How long it runs (' +
                    (inputs.unit === 'mo' ? inputs.horizon + ' mo = ' + (inputs.horizon / 12).toFixed(2) + ' year(s)'
                        : inputs.unit === 'wk' ? inputs.horizon + ' wk ≈ ' + (inputs.horizon / 52).toFixed(2) + ' year(s)'
                            : inputs.horizon + ' day = ' + (inputs.horizon / 365).toFixed(3) + ' year(s))');
                el('f-unit').value = inputs.unit;

                // ---- withdraw buttons ----
                document.querySelectorAll('.withdraw-btn').forEach(function (btn) {
                    var active = parseFloat(btn.getAttribute('data-withdraw')) === inputs.withdraw_pct * 100;
                    btn.className = 'withdraw-btn px-4 py-2 rounded-full text-xs font-bold border transition ' +
                        (active
                            ? 'bg-primary border-primary text-white'
                            : 'border-dark-100 text-gray-400 hover:text-white hover:border-primary');
                });
                el('withdraw-title').textContent = 'Take profits out each ' + unitName + '?';

                // ---- quick start highlight ----
                document.querySelectorAll('.quick-btn').forEach(function (btn) {
                    var active = parseFloat(btn.getAttribute('data-amount')) === inputs.start;
                    btn.className = 'quick-btn px-3 py-1 rounded-full text-xs font-semibold border transition ' +
                        (active
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-dark-100 text-gray-300 hover:border-primary');
                });

                // ---- chart ----
                var labels = series.map(function (row) { return row.label; });
                var gains = series.map(function (row) { return row.gain; });
                var balances = series.map(function (row) { return row.balance; });
                updateChart(labels, gains, balances);
                el('chart-gain-label').textContent = "This " + unitName + "'s gain";

                // ---- advantage note ----
                el('advantage-amount').textContent = signedMoney(summary.compounding_advantage, 0);
                el('simple-note').textContent = 'simple: ' + money(summary.simple_balance, 0);

                // ---- projection table ----
                var titleMap = {
                    mo: 'Month-by-month projection',
                    wk: 'Week-by-week projection',
                    day: 'Day-by-day projection'
                };
                el('projection-title').textContent = titleMap[inputs.unit];
                el('projection-subtitle').textContent = inputs.horizon + ' ' + unitPlural + ' · ' +
                    pct(inputs.rate, inputs.rate % 1 === 0 ? 0 : 1) + ' / ' +
                    (inputs.rate_basis === 'month' ? 'mo' : 'yr');
                el('col-period-1').textContent = unitName;
                el('col-period-2').textContent = unitName;
                renderTable(series);

                // ---- breakdown ----
                el('bd-start').textContent = money(inputs.start, 2);
                el('bd-rate').textContent = pct(inputs.rate, inputs.rate % 1 === 0 ? 0 : 2) + ' per ' +
                    (inputs.rate_basis === 'month' ? 'month' : 'year') +
                    ' (nominal annual ' + pct(summary.nominal_annual_pct, summary.nominal_annual_pct % 1 === 0 ? 0 : 2) + ')';
                el('bd-compounding').textContent = COMPOUNDING_LABEL[inputs.compounding] + ' · ' +
                    inputs.compounds_per_year + '×/year · ' +
                    pct(summary.periodic_rate_pct, summary.periodic_rate_pct < 1 ? 4 : 4) + ' per period';
                el('bd-horizon').textContent = inputs.horizon + ' ' + unitPlural + ' = ' +
                    summary.total_years.toFixed(4) + ' year(s) = ' + summary.periods + ' compounding period(s)';
                el('bd-contribution').textContent = summary.total_contributions > 0
                    ? money(inputs.contribution, 2) + ' × ' + summary.contribution_count + ' ' + unitPlural
                    : 'None';
                el('bd-timing').textContent = 'End of each ' + unitName + ' (earns interest from the next period)';
                el('bd-withdraw').textContent = inputs.withdraw_pct > 0
                    ? 'Take ' + pct(inputs.withdraw_pct * 100, 0) + ' of each period\'s gain as cash'
                    : 'Keep it all in';
                el('bd-contributions').textContent = money(summary.total_contributions, 2);
                el('bd-interest').textContent = money(summary.interest_earned, 2);
                el('bd-final').textContent = money(summary.final_balance, 2);

                el('info-contribution-title').textContent = 'Adding each ' + unitName;
            }

            // ------------------------------ events ------------------------------
            function bindField(id, key, transform) {
                var node = el(id);
                node.addEventListener('input', function () {
                    state[key] = transform ? transform(node.value) : node.value;
                    render();
                });
            }

            bindField('f-start', 'start');
            bindField('f-rate', 'rate');
            bindField('f-horizon', 'horizon');
            bindField('f-contribution', 'contribution');

            el('f-unit').addEventListener('change', function () {
                state.unit = this.value;
                render();
            });

            el('f-compounding').addEventListener('change', function () {
                state.compounding = this.value;
                render();
            });

            document.querySelectorAll('.basis-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    state.rate_basis = btn.getAttribute('data-basis');
                    render();
                });
            });

            document.querySelectorAll('.quick-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    state.start = btn.getAttribute('data-amount');
                    el('f-start').value = state.start;
                    render();
                });
            });

            document.querySelectorAll('.withdraw-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    state.withdraw_pct = btn.getAttribute('data-withdraw');
                    render();
                });
            });

            render();
        })();
    </script>
@endsection
