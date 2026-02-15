<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCopySubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('copy_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('expert_trader_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Amount allocated for copying
            $table->decimal('copy_percentage', 5, 2)->default(100); // Percentage of expert's trades to copy
            $table->enum('status', ['active', 'paused', 'cancelled', 'completed'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->decimal('max_risk_per_trade', 5, 2)->default(10); // Max % of portfolio per trade
            $table->decimal('stop_loss_percentage', 5, 2)->default(20); // Auto stop-loss %
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['expert_trader_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('copy_subscriptions');
    }
}