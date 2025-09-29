<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOldSuperAdminMarqueesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('old_super_admin_marquees', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->text('content');

            $table->datetime('start');
            $table->datetime('end');

            $table->unsignedInteger('business')->nullable();
            $table->foreign('business')->references('id')->on('companies');

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
        Schema::dropIfExists('old_super_admin_marquees');
    }
}
