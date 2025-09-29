<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToEmployeeTaskSubcategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_task_subcategories', function (Blueprint $table) {
            $table->longText('key_activities')->nullable()->after('frequency');
            $table->string('target')->nullable()->after('key_activities');
            $table->string('uom', 20)->nullable()->after('target');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_task_subcategories', function (Blueprint $table) {
            $table->dropColumn(['key_activities','target','uom']);
        });
    }
}
