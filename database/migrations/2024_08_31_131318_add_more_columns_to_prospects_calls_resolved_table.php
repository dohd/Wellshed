<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreColumnsToProspectsCallsResolvedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prospects_calls_resolved', function (Blueprint $table) {
            $table->dropColumn(['erp','current_erp','current_erp_usage','erp_challenges','current_erp_challenges','erp_demo']);
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
        Schema::table('prospects_calls_resolved', function (Blueprint $table) {
            $table->dropColumn(['prospect_question_id']);
        });
    }
}
