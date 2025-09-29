<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRjcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rjcs', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('project_id')->unsigned();
            $table->integer('tid')->nullable();
            $table->integer('reference')->nullable();
            $table->string('technician')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('subject')->nullable();
            $table->string('region')->nullable();
            $table->date('report_date')->nullable();
            $table->string('image_one')->nullable();
            $table->string('image_two')->nullable();
            $table->string('image_three')->nullable();
            $table->string('image_four')->nullable();
            $table->string('caption_one')->nullable();
            $table->string('caption_two')->nullable();
            $table->string('caption_three')->nullable();
            $table->string('caption_four')->nullable();
            $table->string('prepared_by')->nullable();
            $table->integer('ins')->unsigned();
            $table->string('attention')->nullable();
            $table->tinyInteger('report_type')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // foreign key
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rjcs');
    }
}
