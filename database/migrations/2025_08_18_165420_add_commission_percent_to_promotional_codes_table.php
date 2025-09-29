<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionPercentToPromotionalCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotional_codes', function (Blueprint $table) {
            $table->decimal('company_commission_percent', 15,2)->defaultValue(0);
            $table->decimal('cash_back_1_percent', 15,2)->defaultValue(0);
            $table->decimal('cash_back_2_percent', 15,2)->defaultValue(0);
            $table->decimal('cash_back_3_percent', 15,2)->defaultValue(0);
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
            $table->dropColumn(['company_commission_percent','cash_back_1_percent','cash_back_2_percent','cash_back_3_percent']);
        });
    }
}
