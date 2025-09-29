<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValuedColsToJobValuations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuations', function (Blueprint $table) {
            $table->decimal('exp_valuated_perc', 10,4)->default(0);
            $table->decimal('valued_taxable', 16,4)->default(0);
            $table->decimal('valued_subtotal', 16,4)->default(0);
            $table->decimal('valued_tax', 16,4)->default(0);
            $table->decimal('valued_total', 16,4)->default(0);
            $table->decimal('valued_perc', 10,4)->default(0);            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_valuations', function (Blueprint $table) {
            $table->dropColumn(['exp_valuated_perc', 'valued_taxable', 'valued_subtotal', 'valued_tax', 'valued_total', 'valued_perc']);
        });
    }
}
