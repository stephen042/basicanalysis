<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompoundInvestment extends Model
{
    protected $fillable = [
        'user_id', 'payment_method_id', 'payment_method', 'payment_proof',
        'submission_key',
        'amount', 'duration_months', 'monthly_return', 'performance_rate',
        'recurring_amount', 'recurring_frequency', 'deposit_status', 'status',
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
        // Top-ups are the earnings payouts; the initial principal is not
        // credited again at completion.
        return $this->total_topups;
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
