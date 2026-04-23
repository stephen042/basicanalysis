<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignalTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signal_transactions', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('signal_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Snapshot of price at time of purchase
            $table->string('transaction_id')->nullable(); // For blockchain hash or reference
            $table->enum('status', ['pending', 'cancelled', 'approved'])->default('pending');
            $table->timestamp('expires_at')->nullable(); // Calculated when approved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('signal_transactions');
    }
}
