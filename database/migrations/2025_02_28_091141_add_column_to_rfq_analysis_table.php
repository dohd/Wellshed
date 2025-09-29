<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToRfqAnalysisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rfq_analysis', function (Blueprint $table) {
            $table->enum('status', ['pending','approved','rejected','amend'])->default('pending');
            $table->text('status_note')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('approved_date')->nullable();
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
            $table->dropColumn(['status','status_note','approved_by','approved_date']);
        });
    }
}
