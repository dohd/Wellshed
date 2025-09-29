<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyPromotionalPrefixesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_promotional_prefixes', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedInteger('company_id')->unique();
            $table->foreign('company_id')->references('id')->on('companies');

            $table->string('prefix')->unique();

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
        Schema::dropIfExists('company_promotional_prefixes');
    }
}
