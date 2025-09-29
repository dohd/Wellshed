<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionColumnsToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('company_commission', 15,2)->defaultValue(0);
            $table->decimal('commission_1', 15,2)->defaultValue(0);
            $table->decimal('commission_2', 15,2)->defaultValue(0);
            $table->decimal('commission_3', 15,2)->defaultValue(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['company_commission','commission_1','commission_2','commission_3']);
        });
    }
}
