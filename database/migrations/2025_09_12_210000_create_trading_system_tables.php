<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradingSystemTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create prediction_trades table
        if (!Schema::hasTable('prediction_trades')) {
            Schema::create('prediction_trades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('asset_symbol', 10); // BTC, ETH, etc.
                $table->enum('prediction', ['UP', 'DOWN']);
                $table->decimal('trade_amount', 10, 2);
                $table->enum('trade_type', ['fixed_time', 'flexible']);
                $table->integer('duration_minutes')->nullable(); // For fixed_time trades
                $table->decimal('entry_price', 15, 8);
                $table->decimal('exit_price', 15, 8)->nullable();
                $table->decimal('payout_amount', 10, 2)->nullable();
                $table->enum('result', ['pending', 'won', 'lost', 'cancelled'])->default('pending');
                $table->timestamp('entry_time');
                $table->timestamp('expiry_time')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->boolean('is_cancelled')->default(false);
                $table->text('cancellation_reason')->nullable();
                $table->boolean('admin_manipulated')->default(false);
                $table->json('manipulation_log')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'result']);
                $table->index(['asset_symbol', 'entry_time']);
                $table->index(['expiry_time']);
            });
        }

        // Add trading_balance to users table if not exists
        if (!Schema::hasColumn('users', 'trading_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('trading_balance', 15, 2)->default(0.00)->after('account_bal');
                $table->decimal('trading_profit', 15, 2)->default(0.00)->after('trading_balance');
                $table->integer('total_trades')->default(0)->after('trading_profit');
                $table->integer('winning_trades')->default(0)->after('total_trades');
                $table->boolean('trading_enabled')->default(true)->after('winning_trades');
                $table->timestamp('last_trade_at')->nullable()->after('trading_enabled');
            });
        }

        // Create user_trade_controls table
        if (!Schema::hasTable('user_trade_controls')) {
            Schema::create('user_trade_controls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('daily_loss_limit', 10, 2)->default(1000.00);
                $table->decimal('max_trade_amount', 10, 2)->default(500.00);
                $table->integer('daily_trade_limit')->default(50);
                $table->decimal('current_daily_loss', 10, 2)->default(0.00);
                $table->integer('current_daily_trades')->default(0);
                $table->date('reset_date')->default(now()->toDateString());
                $table->boolean('is_restricted')->default(false);
                $table->text('restriction_reason')->nullable();
                $table->timestamp('restriction_until')->nullable();
                $table->decimal('target_win_rate', 5, 2)->nullable(); // Admin set target
                $table->timestamps();
                
                $table->unique('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_trade_controls');
        
        if (Schema::hasColumn('users', 'trading_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'trading_balance', 
                    'trading_profit', 
                    'total_trades', 
                    'winning_trades',
                    'trading_enabled',
                    'last_trade_at'
                ]);
            });
        }
        
        Schema::dropIfExists('prediction_trades');
    }
}
