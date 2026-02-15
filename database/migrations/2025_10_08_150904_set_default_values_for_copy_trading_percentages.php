<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetDefaultValuesForCopyTradingPercentages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw SQL to set default values
        DB::statement('ALTER TABLE users MODIFY copy_trading_profit_percentage DECIMAL(5,2) DEFAULT 5.00');
        DB::statement('ALTER TABLE users MODIFY copy_trading_loss_percentage DECIMAL(5,2) DEFAULT 3.00');
        
        // Update existing NULL values to defaults
        DB::statement('UPDATE users SET copy_trading_profit_percentage = 5.00 WHERE copy_trading_profit_percentage IS NULL');
        DB::statement('UPDATE users SET copy_trading_loss_percentage = 3.00 WHERE copy_trading_loss_percentage IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE users MODIFY copy_trading_profit_percentage DECIMAL(5,2) DEFAULT NULL');
        DB::statement('ALTER TABLE users MODIFY copy_trading_loss_percentage DECIMAL(5,2) DEFAULT NULL');
    }
}
