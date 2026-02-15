<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UserTradingBot;
use App\Models\TradingLog;
use App\Models\TradingAsset;
use App\Models\Tp_Transaction;
use App\Models\Settings;
use App\Mail\TradingBotCompletion;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ProcessTradingBotsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationService;

    public function __construct()
    {
        $this->notificationService = app(NotificationService::class);
    }

    public function handle()
    {
        \Log::info('ProcessTradingBotsJob started at ' . now());

        $activeTradingBots = UserTradingBot::where('status', 'active')
            ->where('expires_at', '>', Carbon::now())
            ->with(['user', 'tradingBot'])
            ->get();

        \Log::info('Found ' . $activeTradingBots->count() . ' active trading bots');

        foreach ($activeTradingBots as $userBot) {
            $this->processUserTradingBot($userBot);
        }

        $this->completeExpiredBots();

        \Log::info('ProcessTradingBotsJob completed at ' . now());
    }

    private function processUserTradingBot(UserTradingBot $userBot)
    {
        try {
            $lastActivity = $userBot->tradingLogs()->latest()->first();
            $intervalMinutes = rand(10, 15);

            if ($lastActivity) {
                $minutesSinceLastActivity = $lastActivity->created_at->diffInMinutes(Carbon::now());
                if ($minutesSinceLastActivity < $intervalMinutes) {
                    return;
                }
            } else {
                $minutesSinceStart = $userBot->created_at->diffInMinutes(Carbon::now());
                if ($minutesSinceStart < 10) {
                    return;
                }
            }

            \Log::info("Generating trading activity for bot {$userBot->id} (User: {$userBot->user_id})");

            $botProfitRate = $userBot->tradingBot->profit_rate;
            $botDurationHours = $userBot->tradingBot->duration;

            $totalMinutes = $botDurationHours * 60;
            $averageIntervalMinutes = 12.5;
            $totalTradingSessions = floor($totalMinutes / $averageIntervalMinutes);

            $targetProfitPerSession = ($botProfitRate / 100) * $userBot->amount / $totalTradingSessions;

            $userProfitRate = $userBot->user->trading_profit_rate ?? 70.00;

            $isProfit = rand(1, 100) <= $userProfitRate;
            $type = $isProfit ? 'profit' : 'loss';

            if ($isProfit) {
                $amount = $targetProfitPerSession * (rand(80, 120) / 100);
            } else {
                $amount = $targetProfitPerSession * (rand(20, 60) / 100);
            }

            $amount = max($amount, 0.01);
            $amount = round($amount, 2);

            $tradingAsset = TradingAsset::where('is_active', true)->inRandomOrder()->first();

            $tradingLog = TradingLog::create([
                'user_trading_bot_id' => $userBot->id,
                'trading_asset_id' => $tradingAsset ? $tradingAsset->id : null,
                'amount' => $amount,
                'type' => $type,
                'asset_price' => $tradingAsset ? $tradingAsset->current_price : null,
                'quantity' => $tradingAsset && $tradingAsset->current_price
                    ? round($amount / $tradingAsset->current_price, 8)
                    : null,
            ]);

            $action = $type === 'profit' ? 'earned profit from' : 'incurred loss from';
            $this->notificationService->sendBotTradeNotification(
                $userBot->user_id,
                $userBot->tradingBot->name,
                $action,
                $amount,
                $tradingAsset ? $tradingAsset->symbol : null
            );

            if ($type === 'profit') {
                $currentTotalProfit = $userBot->tradingLogs->where('type', 'profit')->sum('amount');
                $currentTotalLoss = $userBot->tradingLogs->where('type', 'loss')->sum('amount');
                $currentBalance = $userBot->amount + ($currentTotalProfit - $currentTotalLoss);

                $this->notificationService->sendProfitNotification(
                    $userBot->user_id,
                    $userBot->tradingBot->name,
                    $amount,
                    $currentBalance
                );
            } else {
                $currentTotalProfit = $userBot->tradingLogs->where('type', 'profit')->sum('amount');
                $currentTotalLoss = $userBot->tradingLogs->where('type', 'loss')->sum('amount');
                $currentBalance = $userBot->amount + ($currentTotalProfit - $currentTotalLoss);

                $this->notificationService->sendProfitNotification(
                    $userBot->user_id,
                    $userBot->tradingBot->name,
                    -$amount,
                    $currentBalance
                );
            }

            \Log::info("Created {$type} trade for bot {$userBot->id}: \${$amount} (Target: \${$targetProfitPerSession}, Rate: {$userProfitRate}%)");

        } catch (\Exception $e) {
            \Log::error('Error processing trading bot ' . $userBot->id . ': ' . $e->getMessage());
        }
    }

    private function completeExpiredBots()
    {
        $expiredBots = UserTradingBot::where('status', 'active')
            ->where('expires_at', '<=', Carbon::now())
            ->with(['user', 'tradingBot', 'tradingLogs'])
            ->get();

        foreach ($expiredBots as $userBot) {
            try {
                $totalProfit = $userBot->tradingLogs->where('type', 'profit')->sum('amount');
                $totalLoss = $userBot->tradingLogs->where('type', 'loss')->sum('amount');
                $netProfit = $totalProfit - $totalLoss;

                $returnAmount = $userBot->amount + $netProfit;

                $user = $userBot->user;
                $user->account_bal += $returnAmount;
                $user->roi += $netProfit;
                $user->save();

                if ($netProfit != 0) {
                    $planDescription = $netProfit > 0
                        ? $userBot->tradingBot->name . ' made a profit'
                        : $userBot->tradingBot->name . ' made a loss';

                    Tp_Transaction::create([
                        'user' => $user->id,
                        'plan' => $planDescription,
                        'amount' => abs($netProfit),
                        'type' => 'ROI'
                    ]);
                }

                $userBot->update(['status' => 'completed']);

                $duration = $userBot->created_at->diffForHumans($userBot->expires_at, true);
                $this->notificationService->sendBotCompletionNotification(
                    $user->id,
                    $userBot->tradingBot->name,
                    $returnAmount,
                    $netProfit,
                    $duration
                );

                $this->sendBotCompletionEmail($user, $userBot, $netProfit, $returnAmount);

                $resultType = $netProfit >= 0 ? 'Profit' : 'Loss';
                \Log::info("Completed bot {$userBot->id} for user {$user->id}. Investment: \${$userBot->amount}, Net {$resultType}: \${$netProfit}, Total: \${$returnAmount}");

            } catch (\Exception $e) {
                \Log::error('Error completing expired bot ' . $userBot->id . ': ' . $e->getMessage());
            }
        }
    }

    private function sendBotCompletionEmail($user, $userBot, $netProfit, $returnAmount)
    {
        try {
            $settings = Settings::where('id', 1)->first();

            if (!$settings) {
                \Log::warning('Settings not found for bot completion email');
                return;
            }

            $siteName = $settings->site_name ?? 'Trading Platform';
            $subject = "Trading Bot Session Completed - {$siteName}";

            Mail::to($user->email)->send(new TradingBotCompletion(
                $user,
                $userBot,
                $netProfit,
                $returnAmount,
                $subject
            ));

            \Log::info("Sent bot completion email to user {$user->id} ({$user->email})");

        } catch (\Exception $e) {
            \Log::error("Failed to send bot completion email to user {$user->id}: " . $e->getMessage());
        }
    }
}
