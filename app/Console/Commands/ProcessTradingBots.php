<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserTradingBot;
use App\Models\TradingLog;
use App\Models\User;
use Carbon\Carbon;

class ProcessTradingBots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process active trading bots and generate trading activities';

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
        $this->info('Dispatching trading bot processing job...');

        // Dispatch the job to process trading bots
        \App\Jobs\ProcessTradingBotsJob::dispatch();

        $this->info('Trading bot processing job dispatched successfully!');
        return 0;
    }
}
