<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToProspectsCallListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prospects_call_list', function (Blueprint $table) {
            $table->integer('reassign_status')->default(0)->after('call_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prospects_call_list', function (Blueprint $table) {
            $table->dropColumn('reassign_status');
        });
    }
}
