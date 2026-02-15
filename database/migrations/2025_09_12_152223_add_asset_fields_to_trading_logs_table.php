<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssetFieldsToTradingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trading_logs', function (Blueprint $table) {
            $table->foreignId('trading_asset_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('asset_price', 15, 8)->nullable();
            $table->decimal('quantity', 15, 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trading_logs', function (Blueprint $table) {
            $table->dropForeign(['trading_asset_id']);
            $table->dropColumn(['trading_asset_id', 'asset_price', 'quantity']);
        });
    }
}
