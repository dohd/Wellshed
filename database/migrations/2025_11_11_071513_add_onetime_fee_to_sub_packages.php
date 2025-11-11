<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnetimeFeeToSubPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sub_packages', function (Blueprint $table) {
            $table->decimal('onetime_fee', 12, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sub_packages', function (Blueprint $table) {
            $table->dropColumn(['onetime_fee']);
        });
    }
}
