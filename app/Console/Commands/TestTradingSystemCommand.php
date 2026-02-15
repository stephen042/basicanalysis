<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateTradingPricesJob;
use App\Jobs\ResolveExpiredTradesJob;
use App\Jobs\UpdateWinRateStatsJob;
use App\Jobs\ProcessTradingPayoutsJob;

class TestTradingSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:test {job?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test trading system jobs manually';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $job = $this->argument('job');

        if (!$job) {
            $this->info('Available jobs to test:');
            $this->line('  prices    - Test UpdateTradingPricesJob');
            $this->line('  resolve   - Test ResolveExpiredTradesJob');
            $this->line('  stats     - Test UpdateWinRateStatsJob');
            $this->line('  payouts   - Test ProcessTradingPayoutsJob');
            $this->line('  all       - Test all jobs');
            return 0;
        }

        switch ($job) {
            case 'prices':
                $this->testUpdatePricesJob();
                break;
            case 'resolve':
                $this->testResolveTradesJob();
                break;
            case 'stats':
                $this->testUpdateStatsJob();
                break;
            case 'payouts':
                $this->testPayoutsJob();
                break;
            case 'all':
                $this->testAllJobs();
                break;
            default:
                $this->error("Unknown job: {$job}");
                return 1;
        }

        return 0;
    }

    private function testUpdatePricesJob()
    {
        $this->info('Testing UpdateTradingPricesJob...');
        try {
            $job = new UpdateTradingPricesJob();
            $job->handle();
            $this->info('✅ UpdateTradingPricesJob completed successfully');
        } catch (\Exception $e) {
            $this->error('❌ UpdateTradingPricesJob failed: ' . $e->getMessage());
        }
    }

    private function testResolveTradesJob()
    {
        $this->info('Testing ResolveExpiredTradesJob...');
        try {
            $job = new ResolveExpiredTradesJob();
            $job->handle();
            $this->info('✅ ResolveExpiredTradesJob completed successfully');
        } catch (\Exception $e) {
            $this->error('❌ ResolveExpiredTradesJob failed: ' . $e->getMessage());
        }
    }

    private function testUpdateStatsJob()
    {
        $this->info('Testing UpdateWinRateStatsJob...');
        try {
            $job = new UpdateWinRateStatsJob();
            $job->handle();
            $this->info('✅ UpdateWinRateStatsJob completed successfully');
        } catch (\Exception $e) {
            $this->error('❌ UpdateWinRateStatsJob failed: ' . $e->getMessage());
        }
    }

    private function testPayoutsJob()
    {
        $this->info('Testing ProcessTradingPayoutsJob...');
        try {
            $job = new ProcessTradingPayoutsJob();
            $job->handle();
            $this->info('✅ ProcessTradingPayoutsJob completed successfully');
        } catch (\Exception $e) {
            $this->error('❌ ProcessTradingPayoutsJob failed: ' . $e->getMessage());
        }
    }

    private function testAllJobs()
    {
        $this->info('Testing all trading system jobs...');
        $this->line('');
        
        $this->testUpdatePricesJob();
        $this->line('');
        
        $this->testResolveTradesJob();
        $this->line('');
        
        $this->testUpdateStatsJob();
        $this->line('');
        
        $this->testPayoutsJob();
        $this->line('');
        
        $this->info('🎉 All trading system jobs tested!');
    }
}
