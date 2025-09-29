<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasualLabourersRemunerationLabourAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('casual_labourers_remuneration_labour_allocations', function (Blueprint $table) {

            $table->string('clrla_number');
            $table->primary('clrla_number', 'pk_clrla_number');

            $table->string('clr_number');
            $table->foreign('clr_number', 'clrla_clr_number_foreign')->references('clr_number')->on('casual_labourers_remunerations')->onDelete('cascade');

            $table->bigInteger('labour_allocation_id');
            $table->foreign('labour_allocation_id', 'clrla_labour_allocation_id_foreign')->references('id')->on('labour_allocations')->onDelete('cascade');

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
        Schema::dropIfExists('casual_labourers_remuneration_labour_allocations');
    }
}
