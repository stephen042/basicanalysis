<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaInspector;

class AddSwapAndWithdrawalColumns extends Migration
{
    /**
     * Add the columns required by the working swap conversion system and the
     * crypto/currency withdrawal selection. Every change is guarded so the
     * migration is safe to run against an existing database containing real
     * user records, and never drops data.
     */
    public function up()
    {
        // 1. Swap history: persist the applied exchange rate and fee.
        if (SchemaInspector::hasTable('crypto_records')) {
            Schema::table('crypto_records', function (Blueprint $table) {
                if (!SchemaInspector::hasColumn('crypto_records', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 30, 12)->nullable();
                }
                if (!SchemaInspector::hasColumn('crypto_records', 'fee_percentage')) {
                    $table->decimal('fee_percentage', 8, 4)->nullable();
                }
                if (!SchemaInspector::hasColumn('crypto_records', 'fee_amount')) {
                    $table->decimal('fee_amount', 20, 8)->nullable();
                }
            });
        }

        // 2. Payment methods: associate a currency-type method with a specific
        //    fiat currency (e.g. NGN, USD, EUR, GBP). Crypto methods keep using
        //    "name" as the asset and "network" as the network.
        if (SchemaInspector::hasTable('wdmethods')) {
            Schema::table('wdmethods', function (Blueprint $table) {
                if (!SchemaInspector::hasColumn('wdmethods', 'currency')) {
                    $table->string('currency', 20)->nullable()->after('name');
                }
            });
        }

        // 3. Withdrawals: record the selected method, asset/currency, network,
        //    charge and an idempotency key for duplicate prevention.
        if (SchemaInspector::hasTable('withdrawals')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                if (!SchemaInspector::hasColumn('withdrawals', 'method_id')) {
                    $table->unsignedBigInteger('method_id')->nullable();
                }
                if (!SchemaInspector::hasColumn('withdrawals', 'asset')) {
                    $table->string('asset', 50)->nullable();
                }
                if (!SchemaInspector::hasColumn('withdrawals', 'currency')) {
                    $table->string('currency', 20)->nullable();
                }
                if (!SchemaInspector::hasColumn('withdrawals', 'network')) {
                    $table->string('network', 50)->nullable();
                }
                if (!SchemaInspector::hasColumn('withdrawals', 'charges')) {
                    $table->decimal('charges', 15, 2)->nullable();
                }
                if (!SchemaInspector::hasColumn('withdrawals', 'submission_key')) {
                    $table->string('submission_key', 64)->nullable();
                    $table->unique('submission_key', 'withdrawals_submission_key_unique');
                }
            });
        }
    }

    public function down()
    {
        // Preserve transaction history; no destructive rollback.
    }
}
