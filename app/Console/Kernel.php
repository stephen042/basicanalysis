<?php

namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Settings;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\TestBotReturns::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // === TRADING BOT SYSTEM ===

        // Process trading bots every 2 minutes for active trading
        $schedule->job(new \App\Jobs\ProcessTradingBotsJob)->everyTwoMinutes();

        // Alternative: Keep the command as well for manual testing
        $schedule->command('trading:process')->everyFiveMinutes();

        // === NEW TRADING SYSTEM JOBS ===

        // Fetch trading assets from CoinGecko API every 3 hours
        $schedule->job(new \App\Jobs\FetchCoinGeckoAssetsJob)->everyThreeHours();

        // Update trading prices every 30 seconds
        $schedule->job(new \App\Jobs\UpdateTradingPricesJob)->everyMinute();
      $schedule->job(new \App\Jobs\ProcessExpertTradesJob)->everyFiveMinutes();
        // $schedule->job(new \App\Jobs\UpdateTradingPricesJob)->everyThirtySeconds();

        // Resolve expired trades every minute
        $schedule->job(new \App\Jobs\ResolveExpiredTradesJob)->everyMinute();

        // Update win rate statistics every 5 minutes
        $schedule->job(new \App\Jobs\UpdateWinRateStatsJob)->everyFiveMinutes();

        // Process trading payouts every minute
        $schedule->job(new \App\Jobs\ProcessTradingPayoutsJob)->everyMinute();

        // === COPY TRADING SYSTEM ===
        
        // Process expired copy trading subscriptions and return investments
        $schedule->job(new \App\Jobs\ProcessExpiredCopySubscriptionsJob)->everyFiveMinutes();
        
        //   $schedule->job(new \App\Jobs\ResolveExpiredTradesJob)->everyMinute();
        // Generate expert trades every 30 minutes during trading hours
        // $schedule->job(new \App\Jobs\GenerateExpertTradesJob)->everyThirtyMinutes()->between('09:00', '21:00');

        // Alternative schedule for more frequent generation during peak hours
        // $schedule->job(new \App\Jobs\GenerateExpertTradesJob)->everyTenMinutes()->between('14:00', '18:00');

        // Clean up old performance history data (monthly)
        //  $schedule->call(function () {
        //     \App\Models\ExpertPerformanceHistory::where('date', '<', now()->subMonths(6))->delete();
        // })->monthly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
