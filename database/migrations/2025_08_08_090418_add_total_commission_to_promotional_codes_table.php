<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalCommissionToPromotionalCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotional_codes', function (Blueprint $table) {
            $table->decimal('total_commission',22,2)->default(0);
            $table->enum('total_commission_type',['fixed', 'percentage'])->default('percentage');
            $table->decimal('company_commission',22,2)->default(0);
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
            $table->dropColumn(['total_commission','total_commission_type','company_commission']);
        });
    }
}
