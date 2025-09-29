<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountermeasureFieldsToHealthAndSafetyTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('health_and_safety_tracking', function (Blueprint $table) {

            $table->text('countermeasure')->nullable()->after('comments');

            $table->unsignedInteger('cm_responsible_person')->nullable()->after('countermeasure');
            $table->foreign('cm_responsible_person')->references('id')->on('users');

            $table->date('completion_date')->nullable()->after('cm_responsible_person');
            $table->text('verification')->nullable()->after('completion_date');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('health_and_safety_tracking', function (Blueprint $table) {

            $table->dropColumn('countermeasure');
            $table->dropColumn('cm_responsible_person');
            $table->dropColumn('completion_date');
            $table->dropColumn('verification');

            $table->dropForeign(['cm_responsible_person']);
        });
    }
}
