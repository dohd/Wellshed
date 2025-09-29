<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoqValuationJcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boq_valuation_jcs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('boq_id');
            $table->unsignedBigInteger('boq_valuation_id')->nullable();
            $table->string('reference', 191);
            $table->date('date');
            $table->string('technician', 191);
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->string('equipment', 191)->nullable();
            $table->string('location', 100)->nullable();
            $table->string('fault', 50)->default('none');
            $table->integer('type')->default(1);
            $table->unsignedBigInteger('ins')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boq_valuation_jcs');
    }
}
