<?php

namespace App\Services;

/**
 * Compound interest projection engine.
 *
 * This is the server side twin of public/js/compound-interest-calculator.js.
 * Both implementations run the exact same algorithm:
 *
 *  1. The time horizon is expressed in a selected unit (months / weeks / days)
 *     and is converted to a number of years (12 mo = 52 wk = 365 day = 1 year).
 *  2. The interest rate is entered either per month or per year and is turned
 *     into a nominal annual rate (monthly rate * 12 when the monthly basis is
 *     selected).
 *  3. The balance is compounded at the selected compounding frequency
 *     (daily/weekly/monthly/quarterly/annually) using the periodic rate
 *     i = nominalAnnual / compoundsPerYear.
 *  4. Recurring contributions are added at the END of every contribution
 *     period (month / week / day - matching the selected time unit), so a
 *     contribution only starts earning interest in the following period.
 *  5. Optional profit withdrawal removes the chosen share of every period's
 *     gain from the balance (cash out) before the next period compounds.
 */
class CompoundInterestCalculator
{
    /** Time horizon units: label + how many of them make one year. */
    public const UNITS = [
        'mo' => ['per_year' => 12, 'name' => 'month', 'step' => 'M'],
        'wk' => ['per_year' => 52, 'name' => 'week', 'step' => 'W'],
        'day' => ['per_year' => 365, 'name' => 'day', 'step' => 'D'],
    ];

    /** Compounding frequencies: how many compounding periods one year has. */
    public const COMPOUNDING = [
        'daily' => 365,
        'weekly' => 52,
        'monthly' => 12,
        'quarterly' => 4,
        'annually' => 1,
    ];

    public const RATE_BASES = ['month', 'year'];

    /** Safety caps so a single request can never produce an unbounded loop. */
    public const LIMITS = [
        'start' => 1000000000,
        'rate' => 10000,
        'contribution' => 1000000000,
        'horizon' => ['mo' => 600, 'wk' => 2600, 'day' => 3650],
    ];

    /**
     * Calculate a full projection.
     *
     * @param  array  $input  start, rate, rate_basis, unit, horizon, contribution, compounding, withdraw_pct
     * @return array normalized inputs + summary + period by period series
     */
    public function calculate(array $input): array
    {
        $start = $this->number($input['start'] ?? 0, 0, self::LIMITS['start']);
        $rate = $this->number($input['rate'] ?? 0, 0, self::LIMITS['rate']);
        $contribution = $this->number($input['contribution'] ?? 0, 0, self::LIMITS['contribution']);

        $basis = in_array($input['rate_basis'] ?? 'month', self::RATE_BASES, true)
            ? $input['rate_basis']
            : 'month';

        $unit = array_key_exists($input['unit'] ?? '', self::UNITS) ? $input['unit'] : 'mo';

        $compounding = array_key_exists($input['compounding'] ?? '', self::COMPOUNDING)
            ? $input['compounding']
            : 'monthly';

        $withdrawPct = $this->number($input['withdraw_pct'] ?? 0, 0, 100) / 100;

        $maxHorizon = self::LIMITS['horizon'][$unit];
        $horizon = (int) round($this->number($input['horizon'] ?? 1, 1, $maxHorizon));
        if ($horizon < 1) {
            $horizon = 1;
        }

        $unitPerYear = self::UNITS[$unit]['per_year'];
        $step = self::UNITS[$unit]['step'];
        $unitName = self::UNITS[$unit]['name'];
        $compoundsPerYear = self::COMPOUNDING[$compounding];

        // Horizon expressed in years (12 months = 52 weeks = 365 days = 1 year).
        $totalYears = $horizon / $unitPerYear;

        // Nominal annual rate, in percent.
        $nominalAnnualPct = $basis === 'month' ? $rate * 12 : $rate;
        $nominalAnnual = $nominalAnnualPct / 100;

        // Rate applied on every compounding period.
        $periodicRate = $nominalAnnual / $compoundsPerYear;

        // Number of compounding periods covering the horizon; the final period
        // may be a partial one, it is pro-rated further down.
        $periods = max(1, (int) ceil($totalYears * $compoundsPerYear - 1e-9));

        $events = [];

        for ($m = 1; $m <= $periods; $m++) {
            $periodStart = ($m - 1) / $compoundsPerYear;
            $spanYears = $totalYears - $periodStart;
            if ($spanYears <= 0) {
                break;
            }

            $fraction = min(1.0, $spanYears * $compoundsPerYear);

            $events[] = [
                't' => min($m / $compoundsPerYear, $totalYears),
                'order' => 1,
                'type' => 'grow',
                'factor' => 1 + $periodicRate * $fraction,
            ];
        }

        // One contribution at the end of every contribution period.
        for ($j = 1; $j <= $horizon; $j++) {
            $events[] = [
                't' => $j / $unitPerYear,
                'order' => 2,
                'type' => 'contribute',
            ];
        }

        usort($events, function ($a, $b) {
            if ($a['t'] == $b['t']) {
                return $a['order'] <=> $b['order'];
            }

            return $a['t'] <=> $b['t'];
        });

        $balance = $start;
        $contributed = 0.0;
        $withdrawn = 0.0;
        $grossInterest = 0.0;
        $gainAccum = 0.0;
        $contributionAccum = 0.0;

        $series = [[
            'label' => 'Start',
            'balance' => $balance,
            'principal' => $start,
            'interest' => 0.0,
            'gain' => 0.0,
            'contribution' => 0.0,
        ]];

        $eventCount = count($events);
        $index = 0;

        for ($j = 1; $j <= $horizon; $j++) {
            $boundary = $j / $unitPerYear;

            while ($index < $eventCount && $events[$index]['t'] <= $boundary + 1e-9) {
                $event = $events[$index++];

                if ($event['type'] === 'grow') {
                    $before = $balance;
                    $balance *= $event['factor'];
                    $gain = $balance - $before;
                    $grossInterest += $gain;

                    $withdraw = $gain * $withdrawPct;
                    $balance -= $withdraw;
                    $withdrawn += $withdraw;

                    $gainAccum += $gain - $withdraw;
                } else {
                    $balance += $contribution;
                    $contributed += $contribution;
                    $contributionAccum += $contribution;
                }
            }

            $principal = $start + $contributed;

            $series[] = [
                'label' => $step . $j,
                'balance' => $balance,
                'principal' => $principal,
                'interest' => $balance - $principal,
                'gain' => $gainAccum,
                'contribution' => $contributionAccum,
            ];

            $gainAccum = 0.0;
            $contributionAccum = 0.0;
        }

        // Safety net: apply anything left over (floating point rounding) and
        // keep the final row consistent.
        if ($index < $eventCount) {
            $last = count($series) - 1;

            while ($index < $eventCount) {
                $event = $events[$index++];

                if ($event['type'] === 'grow') {
                    $before = $balance;
                    $balance *= $event['factor'];
                    $gain = $balance - $before;
                    $grossInterest += $gain;

                    $withdraw = $gain * $withdrawPct;
                    $balance -= $withdraw;
                    $withdrawn += $withdraw;

                    $series[$last]['gain'] += $gain - $withdraw;
                } else {
                    $balance += $contribution;
                    $contributed += $contribution;
                    $series[$last]['contribution'] += $contribution;
                }
            }

            $principal = $start + $contributed;
            $series[$last]['balance'] = $balance;
            $series[$last]['principal'] = $principal;
            $series[$last]['interest'] = $balance - $principal;
        }

        $putIn = $start + $contributed;
        $netInterest = $balance - $putIn;

        // Flat simple interest on the same money for the same period of time.
        $simpleInterest = $start * $nominalAnnual * $totalYears;
        for ($j = 1; $j <= $horizon; $j++) {
            $remaining = max(0, $totalYears - ($j / $unitPerYear));
            $simpleInterest += $contribution * $nominalAnnual * $remaining;
        }
        $simpleBalance = $putIn + $simpleInterest;

        return [
            'inputs' => [
                'start' => $start,
                'rate' => $rate,
                'rate_basis' => $basis,
                'unit' => $unit,
                'unit_name' => $unitName,
                'horizon' => $horizon,
                'contribution' => $contribution,
                'compounding' => $compounding,
                'compounds_per_year' => $compoundsPerYear,
                'withdraw_pct' => $withdrawPct,
            ],
            'summary' => [
                'final_balance' => $balance,
                'interest_earned' => $grossInterest,
                'net_interest' => $netInterest,
                'total_contributions' => $contributed,
                'put_in' => $putIn,
                'growth_pct' => $putIn > 0 ? (($balance - $putIn) / $putIn) * 100 : 0,
                'multiple' => $putIn > 0 ? $balance / $putIn : 0,
                'effective_apy' => (pow(1 + $periodicRate, $compoundsPerYear) - 1) * 100,
                'periodic_rate_pct' => $periodicRate * 100,
                'nominal_annual_pct' => $nominalAnnualPct,
                'withdrawn' => $withdrawn,
                'simple_balance' => $simpleBalance,
                'simple_interest' => $simpleInterest,
                'compounding_advantage' => $balance - $simpleBalance,
                'total_years' => $totalYears,
                'periods' => $periods,
                'contribution_count' => $horizon,
            ],
            'series' => $series,
        ];
    }

    /**
     * Sanitize a numeric input: reject non numbers, clamp to [min, max].
     */
    private function number($value, float $min, float $max): float
    {
        if (!is_numeric($value)) {
            return $min;
        }

        return (float) max($min, min($max, (float) $value));
    }
}
