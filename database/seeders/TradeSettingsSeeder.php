<?php

namespace Database\Seeders;

use App\Models\TradeSetting;
use Illuminate\Database\Seeder;

class TradeSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TradeSetting::initializeDefaults();
    }
}
