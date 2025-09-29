<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('recent_customer_emails', function (Blueprint $table) {

            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');
        });

        Schema::table('recent_customer_sms', function (Blueprint $table) {

            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
