<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotAssetsPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trading_bot_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trading_bot_id')->constrained()->onDelete('cascade');
            $table->foreignId('trading_asset_id')->constrained()->onDelete('cascade');
            $table->decimal('allocation_percentage', 5, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['trading_bot_id', 'trading_asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trading_bot_assets');
    }
}
