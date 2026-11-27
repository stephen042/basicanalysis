<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaInspector;

class AddPaymentSourceToCompoundContributions extends Migration
{
    public function up()
    {
        if (!SchemaInspector::hasTable('compound_investment_contributions')) {
            throw new RuntimeException(
                'compound_investment_contributions must exist before adding recurring payment source fields.'
            );
        }

        $addPaymentMethod = !SchemaInspector::hasColumn('compound_investment_contributions', 'payment_method');
        $addBalanceDebited = !SchemaInspector::hasColumn('compound_investment_contributions', 'balance_debited');

        if ($addPaymentMethod || $addBalanceDebited) {
            Schema::table('compound_investment_contributions', function (Blueprint $table) use ($addPaymentMethod, $addBalanceDebited) {
                if ($addPaymentMethod) {
                    $table->string('payment_method', 64)->nullable();
                }
                if ($addBalanceDebited) {
                    $table->boolean('balance_debited')->default(false);
                }
            });
        }
    }

    public function down()
    {
        // Preserve payment-source and refund audit information.
    }
}
