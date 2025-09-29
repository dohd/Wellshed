<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyNoticeImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_notice_images', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('company_notice_id');
            $table->foreign('company_notice_id')->references('id')->on('company_notices');

            $table->string('location');
            $table->string('filename');

            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');

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
        Schema::dropIfExists('company_notice_images');
    }
}
