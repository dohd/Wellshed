<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToProspectRemarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prospect_remarks', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_question_id')->nullable()->after('prospect_id');
            $table->foreign('prospect_question_id')->references('id')->on('prospect_questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prospect_remarks', function (Blueprint $table) {
            $table->dropColumn('prospect_question_id');
        });
    }
}
