<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToRfqAnalysisItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rfq_analysis_items', function (Blueprint $table) {
            $table->text('availability_details')->nullable();
            $table->text('credit_terms')->nullable();
            $table->text('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rfq_analysis_items', function (Blueprint $table) {
            $table->dropColumn(['comment','availability_details','credit_terms']);
        });
    }
}
