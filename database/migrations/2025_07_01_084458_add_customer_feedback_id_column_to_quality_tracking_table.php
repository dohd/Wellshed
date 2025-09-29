<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerFeedbackIdColumnToQualityTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quality_tracking', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_feedback_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quality_tracking', function (Blueprint $table) {
            $table->dropColumn('customer_feedback_id');
        });
    }
}
