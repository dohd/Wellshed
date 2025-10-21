<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tid')->default(0);
            $table->string('name');
            $table->decimal('price', 16,2)->default(0);
            $table->unsignedInteger('duration')->default(0);
            $table->string('features', 255);

            $table->unsignedInteger('is_disabled')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('ins');
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
        Schema::dropIfExists('sub_packages');
    }
}
