<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserColumnsToBankTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_transfers', function (Blueprint $table) {
            $table->enum('user_type',['none','employee','casual','third_party_user'])->default('none');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('casual_id')->nullable();
            $table->unsignedBigInteger('third_party_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_transfers', function (Blueprint $table) {
            $table->dropColumn(['user_type','employee_id','casual_id','third_party_user_id']);
        });
    }
}
