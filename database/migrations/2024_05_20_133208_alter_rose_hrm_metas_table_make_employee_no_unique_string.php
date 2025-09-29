<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRoseHrmMetasTableMakeEmployeeNoUniqueString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hrm_metas', function (Blueprint $table) {
            $table->string('employee_no', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rose_hrm_metas', function (Blueprint $table) {
            //
        });
    }
}
