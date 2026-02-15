<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCopyTradingTotalProfitToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('copy_trading_total_profit', 15, 2)->nullable()->after('copy_trading_win_rate')
                ->comment('Admin-set total profit/loss user will receive when copy trading subscription expires. Overrides actual trading results.');
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
            $table->dropColumn('copy_trading_total_profit');
        });
    }
}
