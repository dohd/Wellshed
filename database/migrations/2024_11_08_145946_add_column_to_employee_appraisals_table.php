<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToEmployeeAppraisalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_appraisals', function (Blueprint $table) {
            $table->unsignedBigInteger('appraisal_type_id')->nullable()->after('supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_appraisals', function (Blueprint $table) {
            $table->dropColumn('appraisal_type_id');
        });
    }
}
