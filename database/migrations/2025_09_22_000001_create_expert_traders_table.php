<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpertTradersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expert_traders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->integer('total_followers')->default(0);
            $table->decimal('roi_percentage', 8, 2)->default(0);
            $table->integer('total_trades')->default(0);
            $table->decimal('win_rate', 5, 2)->default(0); // e.g., 75.50%
            $table->decimal('total_pnl', 15, 2)->default(0);
            $table->decimal('portfolio_value', 15, 2)->default(10000);
            $table->decimal('risk_score', 3, 1)->default(5.0); // 1-10 scale
            $table->integer('experience_years')->default(1);
            $table->string('specialization')->nullable(); // Crypto, Forex, Stocks, etc.
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->decimal('subscription_fee', 10, 2)->default(0); // Monthly fee
            $table->decimal('performance_fee', 5, 2)->default(0); // Percentage of profits
            $table->decimal('min_copy_amount', 10, 2)->default(100);
            $table->decimal('max_copy_amount', 10, 2)->default(10000);
            $table->text('description')->nullable();
            $table->text('trading_strategy')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'roi_percentage']);
            $table->index(['status', 'win_rate']);
            $table->index('last_active_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expert_traders');
    }
}