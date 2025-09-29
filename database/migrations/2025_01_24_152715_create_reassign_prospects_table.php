<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReassignProspectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reassign_prospects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title'); // For "ASEC Jan1 -(2025-01-25)"
            $table->unsignedBigInteger('employee_from_id'); // For "15"
            $table->integer('total_prospects'); // For "2"
            $table->integer('prospect_to_assign'); // For "1"
            $table->date('start_date'); // For "2025-01-25"
            $table->date('end_date'); // For "2025-01-25"
            $table->unsignedBigInteger('employee_id'); // For "207"
            $table->unsignedBigInteger('call_list_id'); // For "27
            $table->unsignedBigInteger('user_id'); 
            $table->unsignedBigInteger('ins'); 
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
        Schema::dropIfExists('reassign_prospects');
    }
}
