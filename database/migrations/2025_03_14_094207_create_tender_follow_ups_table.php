<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenderFollowUpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tender_follow_ups', function (Blueprint $table) {
            //'recipient','remark','date','reminder_date'
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tender_id');
            $table->text('recipient');
            $table->text('remark');
            $table->date('date')->nullable();
            $table->date('reminder_date')->nullable();
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tender_follow_ups');
    }
}
