<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToReconciliationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reconciliations', function (Blueprint $table) {
            $table->integer('is_done')->nullable();
            $table->date('ending_period')->nullable();
            $table->date('reconciled_on')->nullable();
            $table->decimal('ep_uncleared_balance', 16, 4)->default(0);
            $table->decimal('ep_account_balance', 16, 4)->default(0);
            $table->decimal('uncleared_balance_after_ep', 16, 4)->default(0);
            $table->decimal('ro_account_balance', 16, 4)->default(0);
            $table->decimal('cleared_balance_after_ep', 16, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reconciliations', function (Blueprint $table) {
            $table->dropColumn(['is_done', 'ending_period', 'reconciled_on', 'ep_uncleared_balance', 'ep_account_balance', 'uncleared_balance_after_ep', 'ro_account_balance', 'cleared_balance_after_ep']);
        });
    }
}
