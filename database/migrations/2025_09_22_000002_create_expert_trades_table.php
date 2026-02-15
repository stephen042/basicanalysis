<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpertTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expert_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_trader_id')->constrained()->onDelete('cascade');
            $table->foreignId('trading_asset_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['profit', 'loss']);
            $table->decimal('asset_price', 20, 8)->nullable();
            $table->decimal('quantity', 20, 8)->nullable();
            $table->decimal('entry_price', 20, 8)->nullable();
            $table->decimal('exit_price', 20, 8)->nullable();
            $table->decimal('pnl', 15, 2)->default(0);
            $table->enum('trade_direction', ['long', 'short', 'neutral'])->default('neutral');
            $table->enum('status', ['open', 'closed', 'cancelled'])->default('closed');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            
            $table->index(['expert_trader_id', 'created_at']);
            $table->index(['expert_trader_id', 'type']);
            $table->index(['status', 'opened_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expert_trades');
    }
}