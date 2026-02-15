<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoinGeckoFieldsToTradingAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trading_assets', function (Blueprint $table) {
            // Add CoinGecko specific fields
            $table->string('coingecko_id')->nullable()->after('type');
            $table->decimal('market_cap', 20, 2)->nullable()->after('change_24h');
            $table->integer('market_cap_rank')->nullable()->after('market_cap');
            $table->decimal('total_volume', 20, 2)->nullable()->after('market_cap_rank');
            $table->decimal('high_24h', 15, 8)->nullable()->after('total_volume');
            $table->decimal('low_24h', 15, 8)->nullable()->after('high_24h');
            $table->text('description')->nullable()->after('low_24h');
            $table->timestamp('last_updated')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trading_assets', function (Blueprint $table) {
            // Remove CoinGecko fields
            $table->dropColumn([
                'coingecko_id',
                'market_cap',
                'market_cap_rank', 
                'total_volume',
                'high_24h',
                'low_24h',
                'description',
                'last_updated'
            ]);
        });
    }
}
