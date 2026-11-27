<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompoundInvestment extends Model
{
    protected $fillable = [
        'user_id', 'payment_method_id', 'payment_method', 'payment_proof',
        'submission_key',
        'amount', 'currency', 'duration_months', 'monthly_return', 'performance_rate',
        'recurring_amount', 'recurring_frequency', 'payment_source',
        'next_recurring_due_at', 'deposit_status', 'status',
        'approved_at', 'start_date', 'expected_completion_at', 'rejected_at',
        'rejection_reason', 'completed_at', 'completed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'monthly_return' => 'decimal:3',
        'performance_rate' => 'decimal:3',
        'recurring_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'start_date' => 'datetime',
        'expected_completion_at' => 'datetime',
        'next_recurring_due_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topups()
    {
        return $this->hasMany(CompoundInvestmentTopup::class)->orderBy('created_at');
    }

    // Legacy topups are administrator-credited investment earnings.
    public function earnings()
    {
        return $this->topups();
    }

    public function contributions()
    {
        return $this->hasMany(CompoundInvestmentContribution::class)->orderBy('created_at');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(Wdmethod::class, 'payment_method_id');
    }

    public function getTotalTopupsAttribute()
    {
        return round((float) $this->topups->sum('amount'), 2);
    }

    public function getTotalEarningsAttribute()
    {
        return round((float) $this->topups->sum('amount'), 2);
    }

    public function getTotalUserContributionsAttribute()
    {
        $initial = in_array($this->deposit_status, ['approved'], true)
            ? (float) $this->amount
            : 0;

        return round($initial + (float) $this->contributions
            ->where('status', 'approved')
            ->sum('amount'), 2);
    }

    public function getTotalRecurringContributionsAttribute()
    {
        return round((float) $this->contributions
            ->where('status', 'approved')
            ->sum('amount'), 2);
    }

    public function getNextRecurringDueAttribute()
    {
        if ($this->next_recurring_due_at) {
            return $this->next_recurring_due_at;
        }

        // Preserve a useful schedule display for active legacy records.
        $anchor = $this->start_date ?: $this->approved_at;
        if (!$anchor || (float) $this->recurring_amount <= 0) {
            return null;
        }

        return static::nextRecurringDate($anchor, $this->recurring_frequency);
    }

    /**
     * Advance one schedule interval. Kept in one place so every approval path
     * calculates the same next expected date.
     */
    public static function nextRecurringDate($date, $frequency)
    {
        $date = \Illuminate\Support\Carbon::parse($date);

        if ($frequency === 'daily') {
            return $date->copy()->addDay();
        }
        if ($frequency === 'monthly') {
            return $date->copy()->addMonth();
        }

        return $date->copy()->addWeek();
    }

    /**
     * The next expected due date after a user contribution is approved.
     *
     * The contribution's scheduled period is the base, not the approval time.
     * This means an early payment for 3 December advances a weekly schedule to
     * 10 December instead of leaving it on 3 December. If an approval is very
     * late, the schedule rolls forward to the next future period so the user
     * is not shown an already-expired due date.
     */
    public function nextRecurringDueAfterContribution(CompoundInvestmentContribution $contribution, $approvedAt)
    {
        $approvedAt = \Illuminate\Support\Carbon::parse($approvedAt);
        $base = $contribution->scheduled_for
            ?: $this->next_recurring_due_at
            ?: $approvedAt;

        $next = static::nextRecurringDate($base, $this->recurring_frequency);
        while ($next->lessThanOrEqualTo($approvedAt)) {
            $next = static::nextRecurringDate($next, $this->recurring_frequency);
        }

        return $next;
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->status === 'completed') {
            return 100;
        }
        if (!$this->start_date || !$this->expected_completion_at || $this->status !== 'active') {
            return 0;
        }

        $total = max(1, $this->start_date->diffInSeconds($this->expected_completion_at));
        $elapsed = max(0, $this->start_date->diffInSeconds(now(), false));

        return min(100, round(($elapsed / $total) * 100, 1));
    }

    public function getRemainingDaysAttribute()
    {
        if (!$this->expected_completion_at || in_array($this->status, ['completed', 'rejected'], true)) {
            return 0;
        }

        return now()->greaterThanOrEqualTo($this->expected_completion_at)
            ? 0
            : (int) ceil(now()->diffInHours($this->expected_completion_at) / 24);
    }

    public function getDisplayStatusAttribute()
    {
        if ($this->status === 'pending') {
            return $this->payment_proof ? 'Awaiting Admin Approval' : 'Pending Payment';
        }

        return ucfirst($this->status);
    }
}
