<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToHrmMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hrm_metas', function (Blueprint $table) {
            $table->integer('workshift_id')->nullable()->after('department_id');
            $table->foreign('workshift_id')->references('id')->on('workshift');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrm_metas', function (Blueprint $table) {
            $table->dropColumn('workshift_id');
        });
    }
}
