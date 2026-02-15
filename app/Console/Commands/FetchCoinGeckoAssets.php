<?php

namespace App\Console\Commands;

use App\Jobs\FetchCoinGeckoAssetsJob;
use Illuminate\Console\Command;

class FetchCoinGeckoAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:fetch-coingecko 
                            {--coins= : Comma-separated list of CoinGecko coin IDs}
                            {--limit=50 : Number of top coins to fetch (if no specific coins provided)}
                            {--no-update : Do not update existing assets}
                            {--queue : Run the job in the background queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch cryptocurrency assets from CoinGecko API and add them to trading_assets table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Starting CoinGecko assets fetch...');

        // Parse coin IDs if provided
        $coinIds = null;
        if ($this->option('coins')) {
            $coinIds = array_map('trim', explode(',', $this->option('coins')));
            $this->info('Fetching specific coins: ' . implode(', ', $coinIds));
        } else {
            $limit = (int) $this->option('limit');
            $this->info("Fetching top {$limit} cryptocurrencies by market cap");
        }

        // Determine if we should update existing assets
        $updateExisting = !$this->option('no-update');
        if (!$updateExisting) {
            $this->warn('Existing assets will NOT be updated');
        }

        try {
            if ($this->option('queue')) {
                // Dispatch job to queue
                FetchCoinGeckoAssetsJob::dispatch($coinIds, $updateExisting);
                $this->info('✅ Job has been queued! Check logs for progress.');
                $this->line('Run: php artisan queue:work to process the job');
            } else {
                // Run job synchronously
                $this->info('📊 Fetching data from CoinGecko API...');
                $this->withProgressBar(1, function () use ($coinIds, $updateExisting) {
                    FetchCoinGeckoAssetsJob::dispatchSync($coinIds, $updateExisting);
                });
                $this->newLine(2);
                $this->info('✅ CoinGecko assets fetch completed successfully!');
            }

            // Show some statistics
            $this->showStatistics();

        } catch (\Exception $e) {
            $this->error('❌ Error occurred while fetching assets: ' . $e->getMessage());
            $this->line('Check the logs for more details: storage/logs/laravel.log');
            return 1;
        }

        return 0;
    }

    /**
     * Show statistics about trading assets
     */
    private function showStatistics()
    {
        try {
            $totalAssets = \App\Models\TradingAsset::count();
            $cryptoAssets = \App\Models\TradingAsset::where('type', 'crypto')->count();
            $activeAssets = \App\Models\TradingAsset::where('is_active', true)->count();
            $recentlyUpdated = \App\Models\TradingAsset::where('last_updated', '>=', now()->subHour())->count();

            $this->newLine();
            $this->line('📈 <info>Trading Assets Statistics:</info>');
            $this->line("   Total Assets: <comment>{$totalAssets}</comment>");
            $this->line("   Crypto Assets: <comment>{$cryptoAssets}</comment>");
            $this->line("   Active Assets: <comment>{$activeAssets}</comment>");
            $this->line("   Recently Updated: <comment>{$recentlyUpdated}</comment>");

            // Show top 5 assets by market cap
            $topAssets = \App\Models\TradingAsset::where('type', 'crypto')
                ->whereNotNull('market_cap')
                ->orderBy('market_cap_rank')
                ->limit(5)
                ->get(['symbol', 'name', 'current_price', 'change_24h']);

            if ($topAssets->count() > 0) {
                $this->newLine();
                $this->line('🏆 <info>Top 5 Crypto Assets:</info>');
                foreach ($topAssets as $asset) {
                    $changeColor = $asset->change_24h >= 0 ? 'info' : 'error';
                    $changeSymbol = $asset->change_24h >= 0 ? '+' : '';
                    $this->line("   {$asset->symbol}: \${$asset->current_price} (<{$changeColor}>{$changeSymbol}{$asset->change_24h}%</{$changeColor}>)");
                }
            }

        } catch (\Exception $e) {
            $this->warn('Could not fetch statistics: ' . $e->getMessage());
        }
    }
}
