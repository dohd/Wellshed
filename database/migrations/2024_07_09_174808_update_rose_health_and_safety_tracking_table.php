<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRoseHealthAndSafetyTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('health_and_safety_tracking', function (Blueprint $table) {
            $table->text('plan')->nullable()->after('timing');
            $table->text('do')->nullable()->after('plan');
            $table->text('check')->nullable()->after('do');
            $table->text('act')->nullable()->after('check');
            $table->dropColumn('pdca_cycle');
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
            $table->dropColumn(['plan', 'do', 'check', 'act']);
            $table->text('pdca_cycle')->nullable();
        });
    }
}
