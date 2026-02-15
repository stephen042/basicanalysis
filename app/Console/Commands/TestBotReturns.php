<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserTradingBot;
use App\Models\TradingLog;
use App\Models\User;
use Carbon\Carbon;

class TestBotReturns extends Command
{
    protected $signature = 'bot:test-returns {user_id?}';
    protected $description = 'Test and display bot trading returns for a user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return;
            }
            $users = collect([$user]);
        } else {
            $users = User::whereHas('userTradingBots', function($query) {
                $query->where('status', 'active');
            })->take(5)->get();
        }

        $this->info('=== BOT TRADING RETURNS TEST ===');
        $this->line('');

        foreach ($users as $user) {
            $this->info("User: {$user->name} (ID: {$user->id})");
            $this->line("Current Balance: $" . number_format($user->account_bal, 2));
            $this->line('');

            $activeBots = $user->userTradingBots()->where('status', 'active')->with('tradingLogs')->get();
            
            if ($activeBots->isEmpty()) {
                $this->line("❌ No active bots");
                $this->line('');
                continue;
            }

            foreach ($activeBots as $bot) {
                $this->info("  Bot #{$bot->id} - Investment: $" . number_format($bot->amount, 2));
                $this->line("  Started: " . $bot->created_at->diffForHumans());
                $this->line("  Expires: " . $bot->expires_at->diffForHumans());
                
                // Calculate current performance
                $totalProfit = $bot->tradingLogs->where('type', 'profit')->sum('amount');
                $totalLoss = $bot->tradingLogs->where('type', 'loss')->sum('amount');
                $totalReturns = $bot->tradingLogs->where('type', 'return')->sum('amount');
                $netGain = $totalProfit - $totalLoss;
                
                $this->line("  Total Trades: " . $bot->tradingLogs->whereIn('type', ['profit', 'loss'])->count());
                $this->line("  Gross Profit: $" . number_format($totalProfit, 2));
                $this->line("  Total Loss: $" . number_format($totalLoss, 2));
                $this->line("  Net Gain: $" . number_format($netGain, 2));
                $this->line("  Returns Paid: $" . number_format($totalReturns, 2));
                
                // Show recent activity
                $recentLogs = $bot->tradingLogs()->latest()->take(3)->get();
                if ($recentLogs->isNotEmpty()) {
                    $this->line("  Recent Activity:");
                    foreach ($recentLogs as $log) {
                        $symbol = $log->type === 'return' ? '💰' : ($log->type === 'profit' ? '🟢' : '🔴');
                        $this->line("    {$symbol} {$log->type}: $" . number_format($log->amount, 2) . " - " . $log->created_at->diffForHumans());
                    }
                }
                
                $this->line('');
            }
            
            $this->line('=' . str_repeat('=', 50));
            $this->line('');
        }

        $this->info('💡 To manually process bots, run: php artisan trading:process');
        $this->info('🔧 To force periodic returns, run: php artisan queue:work (if jobs are queued)');
    }
}