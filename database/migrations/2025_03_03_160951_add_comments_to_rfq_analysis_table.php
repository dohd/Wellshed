<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentsToRfqAnalysisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rfq_analysis', function (Blueprint $table) {
            $table->text('availability_details')->nullable()->after('date');
            $table->text('credit_terms')->nullable()->after('availability_details');
            $table->text('comment')->nullable()->after('credit_terms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rfq_analysis', function (Blueprint $table) {
            $table->dropColumn(['availability_details','credit_terms','comment']);
        });
    }
}
