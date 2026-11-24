<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfiguredPaymentMethodToDeposits extends Migration
{
    public function up()
    {
        if (\App\Support\SchemaInspector::hasTable('deposits')) {
            $addMethodId = !\App\Support\SchemaInspector::hasColumn('deposits', 'payment_method_id');
            $addNetwork = !\App\Support\SchemaInspector::hasColumn('deposits', 'payment_network');

            if ($addMethodId || $addNetwork) {
                Schema::table('deposits', function (Blueprint $table) use ($addMethodId, $addNetwork) {
                    if ($addMethodId) {
                        $table->unsignedBigInteger('payment_method_id')->nullable();
                    }
                    if ($addNetwork) {
                        $table->string('payment_network')->nullable();
                    }
                });
            }
        }

        foreach (['bnc_transactions', 'cp_transactions'] as $tableName) {
            if (!\App\Support\SchemaInspector::hasTable($tableName)) {
                continue;
            }

            $addMethodId = !\App\Support\SchemaInspector::hasColumn($tableName, 'payment_method_id');
            $addNetwork = !\App\Support\SchemaInspector::hasColumn($tableName, 'payment_network');
            if ($addMethodId || $addNetwork) {
                Schema::table($tableName, function (Blueprint $table) use ($addMethodId, $addNetwork) {
                    if ($addMethodId) {
                        $table->unsignedBigInteger('payment_method_id')->nullable();
                    }
                    if ($addNetwork) {
                        $table->string('payment_network')->nullable();
                    }
                });
            }
        }
    }

    public function down()
    {
        // Keep method references attached to existing deposit and gateway records.
    }
}
