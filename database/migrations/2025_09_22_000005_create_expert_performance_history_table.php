<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpertPerformanceHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expert_performance_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_trader_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('portfolio_value', 15, 2);
            $table->decimal('daily_pnl', 15, 2);
            $table->integer('total_trades')->default(0);
            $table->integer('winning_trades')->default(0);
            $table->decimal('roi_percentage', 8, 2)->default(0);
            $table->decimal('drawdown_percentage', 8, 2)->default(0);
            $table->decimal('volume_traded', 15, 2)->default(0);
            $table->integer('followers_count')->default(0);
            $table->timestamps();
            
            $table->unique(['expert_trader_id', 'date']);
            $table->index(['expert_trader_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expert_performance_history');
    }
}