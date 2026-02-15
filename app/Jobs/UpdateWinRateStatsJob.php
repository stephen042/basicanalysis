<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PredictionTrade;
use App\Models\User;
use App\Models\TradeSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateWinRateStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->updateGlobalWinRateStats();
            $this->updateUserWinRateStats();
            $this->updateAssetPerformanceStats();
            $this->cleanupOldStats();
            
            Log::info('Win rate statistics updated successfully');
            
        } catch (\Exception $e) {
            Log::error('Error updating win rate statistics: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    /**
     * Update global platform win rate statistics
     */
    private function updateGlobalWinRateStats(): void
    {
        $timeframes = [
            '1h' => Carbon::now()->subHour(),
            '24h' => Carbon::now()->subDay(),
            '7d' => Carbon::now()->subWeek(),
            '30d' => Carbon::now()->subMonth()
        ];

        foreach ($timeframes as $period => $startTime) {
            $stats = PredictionTrade::where('created_at', '>=', $startTime)
                ->whereIn('status', ['won', 'lost'])
                ->select([
                    DB::raw('COUNT(*) as total_trades'),
                    DB::raw('SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as winning_trades'),
                    DB::raw('SUM(trade_amount) as total_volume'),
                    DB::raw('SUM(CASE WHEN status = "won" THEN actual_payout ELSE 0 END) as total_payouts'),
                    DB::raw('SUM(profit_loss) as net_profit_loss'),
                    DB::raw('AVG(trade_amount) as avg_trade_amount')
                ])
                ->first();

            $winRate = $stats->total_trades > 0 ? 
                round(($stats->winning_trades / $stats->total_trades) * 100, 2) : 0;

            $profitMargin = $stats->total_volume > 0 ? 
                round((($stats->total_volume - $stats->total_payouts) / $stats->total_volume) * 100, 2) : 0;

            // Store in settings for easy access
            TradeSetting::updateOrCreate(
                ['setting_key' => "global_win_rate_{$period}"],
                [
                    'setting_value' => json_encode([
                        'win_rate' => $winRate,
                        'total_trades' => $stats->total_trades,
                        'total_volume' => $stats->total_volume,
                        'profit_margin' => $profitMargin,
                        'net_profit_loss' => $stats->net_profit_loss,
                        'avg_trade_amount' => $stats->avg_trade_amount,
                        'updated_at' => now()->toISOString()
                    ]),
                    'description' => "Global platform statistics for {$period}"
                ]
            );
        }
    }

    /**
     * Update individual user win rate statistics
     */
    private function updateUserWinRateStats(): void
    {
        // Get users who have traded in the last 7 days
        $activeUsers = User::whereHas('predictionTrades', function($query) {
            $query->where('created_at', '>=', Carbon::now()->subWeek());
        })->get();

        foreach ($activeUsers as $user) {
            $this->updateSingleUserStats($user);
        }
    }

    /**
     * Update statistics for a single user
     */
    private function updateSingleUserStats($user): void
    {
        $timeframes = [
            '24h' => Carbon::now()->subDay(),
            '7d' => Carbon::now()->subWeek(),
            '30d' => Carbon::now()->subMonth()
        ];

        foreach ($timeframes as $period => $startTime) {
            $userTrades = $user->predictionTrades()
                ->where('created_at', '>=', $startTime)
                ->whereIn('status', ['won', 'lost'])
                ->get();

            if ($userTrades->isEmpty()) continue;

            $totalTrades = $userTrades->count();
            $winningTrades = $userTrades->where('status', 'won')->count();
            $totalVolume = $userTrades->sum('trade_amount');
            $totalPayout = $userTrades->where('status', 'won')->sum('actual_payout');
            $netProfitLoss = $userTrades->sum('profit_loss');

            $winRate = $totalTrades > 0 ? 
                round(($winningTrades / $totalTrades) * 100, 2) : 0;

            // Store user-specific stats (could be in a separate table)
            TradeSetting::updateOrCreate(
                ['setting_key' => "user_{$user->id}_stats_{$period}"],
                [
                    'setting_value' => json_encode([
                        'win_rate' => $winRate,
                        'total_trades' => $totalTrades,
                        'winning_trades' => $winningTrades,
                        'total_volume' => $totalVolume,
                        'total_payout' => $totalPayout,
                        'net_profit_loss' => $netProfitLoss,
                        'updated_at' => now()->toISOString()
                    ]),
                    'description' => "User {$user->id} statistics for {$period}"
                ]
            );
        }

        // Update user's main record with overall stats
        $allTimeTrades = $user->predictionTrades()
            ->whereIn('status', ['won', 'lost'])
            ->get();

        if ($allTimeTrades->isNotEmpty()) {
            $totalTrades = $allTimeTrades->count();
            $winningTrades = $allTimeTrades->where('status', 'won')->count();
            $totalProfitLoss = $allTimeTrades->sum('profit_loss');
            $winRate = $totalTrades > 0 ? 
                round(($winningTrades / $totalTrades) * 100, 2) : 0;

            $user->update([
                'total_trades' => $totalTrades,
                'winning_trades' => $winningTrades,
                'losing_trades' => $totalTrades - $winningTrades,
                'total_profit_loss' => $totalProfitLoss,
                'win_rate' => $winRate
            ]);
        }
    }

    /**
     * Update asset performance statistics
     */
    private function updateAssetPerformanceStats(): void
    {
        $assets = DB::table('prediction_trades')
            ->join('trading_assets', 'prediction_trades.trading_asset_id', '=', 'trading_assets.id')
            ->where('prediction_trades.created_at', '>=', Carbon::now()->subWeek())
            ->whereIn('prediction_trades.status', ['won', 'lost'])
            ->select([
                'trading_assets.id',
                'trading_assets.name',
                'trading_assets.symbol',
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN prediction_trades.status = "won" THEN 1 ELSE 0 END) as winning_trades'),
                DB::raw('SUM(prediction_trades.trade_amount) as total_volume'),
                DB::raw('AVG(prediction_trades.trade_amount) as avg_trade_amount'),
                DB::raw('SUM(prediction_trades.profit_loss) as net_profit_loss')
            ])
            ->groupBy('trading_assets.id', 'trading_assets.name', 'trading_assets.symbol')
            ->get();

        foreach ($assets as $asset) {
            $winRate = $asset->total_trades > 0 ? 
                round(($asset->winning_trades / $asset->total_trades) * 100, 2) : 0;

            TradeSetting::updateOrCreate(
                ['setting_key' => "asset_{$asset->id}_stats_7d"],
                [
                    'setting_value' => json_encode([
                        'asset_name' => $asset->name,
                        'asset_symbol' => $asset->symbol,
                        'win_rate' => $winRate,
                        'total_trades' => $asset->total_trades,
                        'winning_trades' => $asset->winning_trades,
                        'total_volume' => $asset->total_volume,
                        'avg_trade_amount' => round($asset->avg_trade_amount, 2),
                        'net_profit_loss' => $asset->net_profit_loss,
                        'updated_at' => now()->toISOString()
                    ]),
                    'description' => "Asset {$asset->name} statistics for 7 days"
                ]
            );
        }
    }

    /**
     * Clean up old statistical data
     */
    private function cleanupOldStats(): void
    {
        // Remove user stats older than 90 days
        TradeSetting::where('setting_key', 'LIKE', 'user_%_stats_%')
            ->where('updated_at', '<', Carbon::now()->subDays(90))
            ->delete();

        // Remove asset stats older than 30 days
        TradeSetting::where('setting_key', 'LIKE', 'asset_%_stats_%')
            ->where('updated_at', '<', Carbon::now()->subDays(30))
            ->delete();
    }
}
