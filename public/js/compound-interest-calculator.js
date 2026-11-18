/**
 * Compound interest projection engine (browser side).
 *
 * Twin of app/Services/CompoundInterestCalculator.php - both run the exact
 * same algorithm so the page, the JSON endpoint and the tests agree:
 *
 *   1. Horizon is expressed in months / weeks / days (12 mo = 52 wk = 365 d = 1y).
 *   2. The rate is entered per month or per year and converted to a nominal
 *      annual rate (monthly rate * 12 on the monthly basis).
 *   3. Growth is applied at the selected compounding frequency using the
 *      periodic rate i = nominalAnnual / compoundsPerYear.
 *   4. Recurring contributions are added at the END of every contribution
 *      period (month / week / day, matching the selected time unit).
 *   5. Optional profit withdrawal removes the chosen share of every period's
 *      gain as cash before the next period compounds.
 *
 * Loaded as a plain browser global (window.CompoundEngine) and as a CommonJS
 * module so the same code can be exercised from node during testing.
 */
(function (root, factory) {
    if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.CompoundEngine = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    var UNITS = {
        mo: { perYear: 12, name: 'month', step: 'M' },
        wk: { perYear: 52, name: 'week', step: 'W' },
        day: { perYear: 365, name: 'day', step: 'D' }
    };

    var COMPOUNDING = {
        daily: 365,
        weekly: 52,
        monthly: 12,
        quarterly: 4,
        annually: 1
    };

    var RATE_BASES = ['month', 'year'];

    var LIMITS = {
        start: 1000000000,
        rate: 10000,
        contribution: 1000000000,
        horizon: { mo: 600, wk: 2600, day: 3650 }
    };

    function number(value, min, max) {
        var parsed = typeof value === 'number' ? value : parseFloat(value);
        if (!isFinite(parsed)) {
            return min;
        }
        return Math.max(min, Math.min(max, parsed));
    }

    /**
     * Validate + normalize raw input coming from the UI.
     * Returns { value: {...}, errors: { field: message } }.
     */
    function sanitize(raw) {
        raw = raw || {};
        var errors = {};

        var startRaw = raw.start;
        if (startRaw === '' || startRaw === null || typeof startRaw === 'undefined' || isNaN(parseFloat(startRaw))) {
            errors.start = 'Enter a starting amount.';
        } else if (parseFloat(startRaw) < 0) {
            errors.start = 'Starting amount cannot be negative.';
        }

        var rateRaw = raw.rate;
        if (rateRaw === '' || rateRaw === null || typeof rateRaw === 'undefined' || isNaN(parseFloat(rateRaw))) {
            errors.rate = 'Enter an interest rate.';
        } else if (parseFloat(rateRaw) < 0) {
            errors.rate = 'Interest rate cannot be negative.';
        } else if (parseFloat(rateRaw) > LIMITS.rate) {
            errors.rate = 'Interest rate is capped at ' + LIMITS.rate + '%.';
        }

        var contributionRaw = raw.contribution;
        if (contributionRaw === '' || contributionRaw === null || typeof contributionRaw === 'undefined' || isNaN(parseFloat(contributionRaw))) {
            errors.contribution = 'Enter a contribution (0 for none).';
        } else if (parseFloat(contributionRaw) < 0) {
            errors.contribution = 'Contribution cannot be negative.';
        }

        var unit = UNITS[raw.unit] ? raw.unit : 'mo';
        var horizonRaw = raw.horizon;
        var horizonParsed = parseInt(horizonRaw, 10);
        if (isNaN(horizonParsed) || horizonParsed < 1) {
            errors.horizon = 'Time horizon must be at least 1 ' + UNITS[unit].name + '.';
        } else if (horizonParsed > LIMITS.horizon[unit]) {
            errors.horizon = 'Time horizon is capped at ' + LIMITS.horizon[unit] + ' ' +
                (horizonParsed === 1 ? UNITS[unit].name : UNITS[unit].name + 's') + '.';
        }

        var withdraw = parseFloat(raw.withdraw_pct);
        if (!isFinite(withdraw) || withdraw < 0 || withdraw > 100) {
            withdraw = 0;
        }

        return {
            value: {
                start: number(raw.start, 0, LIMITS.start),
                rate: number(raw.rate, 0, LIMITS.rate),
                rate_basis: RATE_BASES.indexOf(raw.rate_basis) !== -1 ? raw.rate_basis : 'month',
                unit: unit,
                horizon: Math.max(1, Math.min(LIMITS.horizon[unit], isNaN(horizonParsed) ? 1 : horizonParsed)),
                contribution: number(raw.contribution, 0, LIMITS.contribution),
                compounding: COMPOUNDING[raw.compounding] ? raw.compounding : 'monthly',
                withdraw_pct: withdraw
            },
            errors: errors
        };
    }

    function calculate(input) {
        var clean = sanitize(input);
        var state = clean.value;
        var errors = clean.errors;

        var start = state.start;
        var rate = state.rate;
        var contribution = state.contribution;
        var basis = state.rate_basis;
        var unit = state.unit;
        var horizon = state.horizon;
        var compounding = state.compounding;
        var withdrawPct = state.withdraw_pct / 100;

        var unitPerYear = UNITS[unit].perYear;
        var step = UNITS[unit].step;
        var unitName = UNITS[unit].name;
        var compoundsPerYear = COMPOUNDING[compounding];

        var totalYears = horizon / unitPerYear;
        var nominalAnnualPct = basis === 'month' ? rate * 12 : rate;
        var nominalAnnual = nominalAnnualPct / 100;
        var periodicRate = nominalAnnual / compoundsPerYear;

        var periods = Math.max(1, Math.ceil(totalYears * compoundsPerYear - 1e-9));

        var events = [];
        var m;
        for (m = 1; m <= periods; m++) {
            var periodStart = (m - 1) / compoundsPerYear;
            var spanYears = totalYears - periodStart;
            if (spanYears <= 0) {
                break;
            }
            var fraction = Math.min(1, spanYears * compoundsPerYear);
            events.push({
                t: Math.min(m / compoundsPerYear, totalYears),
                order: 1,
                type: 'grow',
                factor: 1 + periodicRate * fraction
            });
        }

        var j;
        for (j = 1; j <= horizon; j++) {
            events.push({ t: j / unitPerYear, order: 2, type: 'contribute' });
        }

        events.sort(function (a, b) {
            if (a.t === b.t) {
                return a.order - b.order;
            }
            return a.t - b.t;
        });

        var balance = start;
        var contributed = 0;
        var withdrawn = 0;
        var grossInterest = 0;
        var gainAccum = 0;
        var contributionAccum = 0;

        var series = [{
            label: 'Start',
            balance: balance,
            principal: start,
            interest: 0,
            gain: 0,
            contribution: 0
        }];

        var eventCount = events.length;
        var index = 0;

        for (j = 1; j <= horizon; j++) {
            var boundary = j / unitPerYear;

            while (index < eventCount && events[index].t <= boundary + 1e-9) {
                var event = events[index++];
                if (event.type === 'grow') {
                    var before = balance;
                    balance *= event.factor;
                    var gain = balance - before;
                    grossInterest += gain;

                    var withdraw = gain * withdrawPct;
                    balance -= withdraw;
                    withdrawn += withdraw;

                    gainAccum += gain - withdraw;
                } else {
                    balance += contribution;
                    contributed += contribution;
                    contributionAccum += contribution;
                }
            }

            var principal = start + contributed;
            series.push({
                label: step + j,
                balance: balance,
                principal: principal,
                interest: balance - principal,
                gain: gainAccum,
                contribution: contributionAccum
            });

            gainAccum = 0;
            contributionAccum = 0;
        }

        if (index < eventCount) {
            var last = series.length - 1;
            while (index < eventCount) {
                var leftover = events[index++];
                if (leftover.type === 'grow') {
                    var b2 = balance;
                    balance *= leftover.factor;
                    var g2 = balance - b2;
                    grossInterest += g2;
                    var w2 = g2 * withdrawPct;
                    balance -= w2;
                    withdrawn += w2;
                    series[last].gain += g2 - w2;
                } else {
                    balance += contribution;
                    contributed += contribution;
                    series[last].contribution += contribution;
                }
            }
            var p2 = start + contributed;
            series[last].balance = balance;
            series[last].principal = p2;
            series[last].interest = balance - p2;
        }

        var putIn = start + contributed;
        var netInterest = balance - putIn;

        var simpleInterest = start * nominalAnnual * totalYears;
        for (j = 1; j <= horizon; j++) {
            var remaining = Math.max(0, totalYears - (j / unitPerYear));
            simpleInterest += contribution * nominalAnnual * remaining;
        }
        var simpleBalance = putIn + simpleInterest;

        return {
            errors: errors,
            inputs: {
                start: start,
                rate: rate,
                rate_basis: basis,
                unit: unit,
                unit_name: unitName,
                horizon: horizon,
                contribution: contribution,
                compounding: compounding,
                compounds_per_year: compoundsPerYear,
                withdraw_pct: withdrawPct
            },
            summary: {
                final_balance: balance,
                interest_earned: grossInterest,
                net_interest: netInterest,
                total_contributions: contributed,
                put_in: putIn,
                growth_pct: putIn > 0 ? ((balance - putIn) / putIn) * 100 : 0,
                multiple: putIn > 0 ? balance / putIn : 0,
                effective_apy: (Math.pow(1 + periodicRate, compoundsPerYear) - 1) * 100,
                periodic_rate_pct: periodicRate * 100,
                nominal_annual_pct: nominalAnnualPct,
                withdrawn: withdrawn,
                simple_balance: simpleBalance,
                simple_interest: simpleInterest,
                compounding_advantage: balance - simpleBalance,
                total_years: totalYears,
                periods: periods,
                contribution_count: horizon
            },
            series: series
        };
    }

    return {
        UNITS: UNITS,
        COMPOUNDING: COMPOUNDING,
        LIMITS: LIMITS,
        sanitize: sanitize,
        calculate: calculate
    };
}));
