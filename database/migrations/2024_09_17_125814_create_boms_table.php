<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tid');
            $table->string('name');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('boq_id');
            $table->foreign('boq_id')->references('id')->on('boqs');
            $table->unsignedBigInteger('lead_id');
            $table->decimal('subtotal', 16,4)->default(0);
            $table->decimal('tax', 16,4)->default(0);
            $table->decimal('taxable', 16,4)->default(0);
            $table->decimal('total', 16,4)->default(0);
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
        Schema::dropIfExists('boms');
    }
}
