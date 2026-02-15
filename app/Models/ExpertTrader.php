<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExpertTrader extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'avatar',
        'total_followers',
        'roi_percentage',
        'total_trades',
        'win_rate',
        'total_pnl',
        'portfolio_value',
        'risk_score',
        'experience_years',
        'specialization',
        'status',
        'subscription_fee',
        'performance_fee',
        'min_copy_amount',
        'max_copy_amount',
        'description',
        'trading_strategy',
        'last_active_at'
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'roi_percentage' => 'decimal:2',
        'win_rate' => 'decimal:2',
        'total_pnl' => 'decimal:2',
        'portfolio_value' => 'decimal:2',
        'risk_score' => 'decimal:1',
        'subscription_fee' => 'decimal:2',
        'performance_fee' => 'decimal:2',
        'min_copy_amount' => 'decimal:2',
        'max_copy_amount' => 'decimal:2',
    ];

    // Relationships
    public function expertTrades()
    {
        return $this->hasMany(ExpertTrade::class);
    }

    public function copySubscriptions()
    {
        return $this->hasMany(CopySubscription::class);
    }

    public function performanceHistory()
    {
        return $this->hasMany(ExpertPerformanceHistory::class);
    }

    // Dynamic Accessors for Real-time Calculations
    public function getCurrentRoiAttribute()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $recentTrades = $this->expertTrades()
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get();

        if ($recentTrades->isEmpty()) {
            return $this->roi_percentage ?? 0;
        }

        $totalProfit = $recentTrades->where('type', 'profit')->sum('amount');
        $totalLoss = $recentTrades->where('type', 'loss')->sum('amount');
        $netPnl = $totalProfit - $totalLoss;

        $baseAmount = $this->portfolio_value ?? 10000;
        return ($netPnl / $baseAmount) * 100;
    }

    public function getCurrent7dPnlAttribute()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $recentTrades = $this->expertTrades()
            ->where('created_at', '>=', $sevenDaysAgo)
            ->get();

        $totalProfit = $recentTrades->where('type', 'profit')->sum('amount');
        $totalLoss = $recentTrades->where('type', 'loss')->sum('amount');
        
        return $totalProfit - $totalLoss;
    }

    public function getCurrentWinRateAttribute()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $recentTrades = $this->expertTrades()
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get();

        if ($recentTrades->isEmpty()) {
            return $this->win_rate ?? 75.0;
        }

        $totalTrades = $recentTrades->count();
        $winningTrades = $recentTrades->where('type', 'profit')->count();

        return $totalTrades > 0 ? ($winningTrades / $totalTrades) * 100 : 0;
    }

    public function getCurrentPortfolioValueAttribute()
    {
        $allTrades = $this->expertTrades()->get();
        $totalProfit = $allTrades->where('type', 'profit')->sum('amount');
        $totalLoss = $allTrades->where('type', 'loss')->sum('amount');
        $netPnl = $totalProfit - $totalLoss;

        $basePortfolio = $this->portfolio_value ?? 10000;
        return $basePortfolio + $netPnl;
    }

    public function getPerformanceChartDataAttribute()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $chartData = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayTrades = $this->expertTrades()
                ->whereDate('created_at', $date)
                ->get();
            
            $dayProfit = $dayTrades->where('type', 'profit')->sum('amount');
            $dayLoss = $dayTrades->where('type', 'loss')->sum('amount');
            $dayPnl = $dayProfit - $dayLoss;
            
            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'pnl' => round($dayPnl, 2),
                'cumulative' => round(
                    $this->expertTrades()
                        ->where('created_at', '<=', $date->endOfDay())
                        ->get()
                        ->groupBy('type')
                        ->map(fn($group) => $group->sum('amount'))
                        ->pipe(fn($grouped) => ($grouped['profit'] ?? 0) - ($grouped['loss'] ?? 0)),
                    2
                )
            ];
        }
        
        return $chartData;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTopPerformers($query, $limit = 10)
    {
        return $query->active()
            ->orderByDesc('roi_percentage')
            ->orderByDesc('win_rate')
            ->limit($limit);
    }

    public function scopeRecentlyActive($query)
    {
        return $query->where('last_active_at', '>=', Carbon::now()->subHours(24));
    }

    // Helper Methods
    public function isOnline()
    {
        return $this->last_active_at && $this->last_active_at->diffInMinutes(Carbon::now()) <= 15;
    }

    public function getRiskLevelText()
    {
        if ($this->risk_score <= 3) return 'Low Risk';
        if ($this->risk_score <= 6) return 'Medium Risk';
        return 'High Risk';
    }

    public function getRiskColorClass()
    {
        if ($this->risk_score <= 3) return 'text-green-400';
        if ($this->risk_score <= 6) return 'text-yellow-400';
        return 'text-red-400';
    }

    public function getActiveSubscribersCount()
    {
        $activeCount = $this->copySubscriptions()
            ->where('status', 'active')
            ->count();
        
        // Add the base total_followers set by admin
        return $activeCount + ($this->total_followers ?? 0);
    }

    public function getTotalCopiedAmount()
    {
        return $this->copySubscriptions()
            ->where('status', 'active')
            ->sum('amount');
    }
}