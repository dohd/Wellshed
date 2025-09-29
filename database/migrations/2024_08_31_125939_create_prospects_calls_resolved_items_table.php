<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProspectsCallsResolvedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prospects_calls_resolved_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('prospect_call_resolved_id');
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('prospect_question_id');
            $table->foreign('prospect_question_id')->references('id')->on('prospect_questions')->onDelete('cascade');
            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')->references('id')->on('prospect_question_items')->onDelete('cascade');
            $table->enum('answer_type',['yes','no','naration']);
            $table->text('explanation')->nullable();
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
        Schema::dropIfExists('prospects_calls_resolved_items');
    }
}
