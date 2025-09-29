<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasualLabourersRemunerationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('casual_labourers_remunerations', function (Blueprint $table) {

            $table->string('clr_number')->primary();
            $table->text('title');
            $table->date('date');
            $table->text('description');

            $table->bigInteger('labour_allocation_id');
            $table->foreign('labour_allocation_id')->references('id')->on('labour_allocations');

            $table->decimal('total_amount', 20, 2);

            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');

            $table->unsignedInteger('updated_by');
            $table->foreign('updated_by')->references('id')->on('users');

            $table->unsignedInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users');

            $table->enum('status', ['APPROVED', 'ON HOLD', 'REJECTED', 'PENDING'])->default('PENDING');

            $table->text('approval_note')->nullable();

            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('casual_labourers_remunerations');
    }
}
