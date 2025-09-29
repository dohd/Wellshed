<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseClassesTableV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_classes', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('name');

            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');

            // Add other fields as needed
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
        Schema::dropIfExists('purchase_classes');
    }
}
