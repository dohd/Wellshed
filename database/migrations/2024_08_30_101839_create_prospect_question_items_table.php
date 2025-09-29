<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProspectQuestionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prospect_question_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('prospect_question_id');
            $table->text('question');
            $table->enum('type',['yes_no', 'naration'])->default('yes_no');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ins');
            $table->foreign('prospect_question_id')->references('id')->on('prospect_questions')->onDelete('cascade');
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
        Schema::dropIfExists('prospect_question_items');
    }
}
