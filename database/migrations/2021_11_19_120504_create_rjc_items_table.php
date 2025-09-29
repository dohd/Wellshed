<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRjcItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rjc_items', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('rjc_id')->unsigned();
            $table->string('tag_number')->nullable();
            $table->string('make')->nullable();
            $table->string('equipment_type')->nullable();
            $table->string('joc_card')->nullable();
            $table->string('capacity')->nullable();
            $table->string('location')->nullable();
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->integer('ins')->unsigned();
            $table->bigInteger('row_index');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // foreign key
            $table->foreign('rjc_id')->references('id')->on('rjcs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rjc_items');
    }
}
