<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionAmtsToPromotionalCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotional_codes', function (Blueprint $table) {
            $table->decimal('company_commission_amount', 22,2)->default(0);
            $table->decimal('cash_back_1_amount', 22,2)->default(0);
            $table->decimal('cash_back_2_amount', 22,2)->default(0);
            $table->decimal('cash_back_3_amount', 22,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promotional_codes', function (Blueprint $table) {
            $table->dropColumn(['cash_back_3_amount','cash_back_2_amount','cash_back_1_amount','company_commission_amount']);
        });
    }
}
