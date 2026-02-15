<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCopyTradingTotalProfitToProfitPercentage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('copy_trading_total_profit');
            $table->decimal('copy_trading_profit_percentage', 5, 2)->nullable()->after('copy_trading_win_rate')
                ->comment('Admin-set profit percentage user gets per winning trade (e.g., 3.5 means 3.5% profit per trade)');
            $table->decimal('copy_trading_loss_percentage', 5, 2)->nullable()->after('copy_trading_profit_percentage')
                ->comment('Admin-set loss percentage user loses per losing trade (e.g., 2.0 means 2.0% loss per trade)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['copy_trading_profit_percentage', 'copy_trading_loss_percentage']);
            $table->decimal('copy_trading_total_profit', 15, 2)->nullable()->after('copy_trading_win_rate');
        });
    }
}
