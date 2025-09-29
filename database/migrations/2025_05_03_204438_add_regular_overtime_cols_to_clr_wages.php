<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegularOvertimeColsToClrWages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clr_wages', function (Blueprint $table) {
            $table->decimal('overtime_hrs', 10,2)->default(0);
            $table->decimal('regular_hrs', 10,2)->default(0);
            $table->decimal('ot_multiplier', 10,2)->default(0);
            $table->decimal('overtime_total', 16,4)->default(0);
            $table->decimal('regular_total', 16,4)->default(0);
            $table->decimal('wage_subtotal', 16,4)->default(0);
            $table->decimal('wage_total', 16,4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clr_wages', function (Blueprint $table) {
            $table->dropColumn(['overtime_hrs', 'regular_hrs', 'ot_multiplier', 'overtime_total', 'regular_total', 'wage_subtotal', 'wage_total']);
        });
    }
}
