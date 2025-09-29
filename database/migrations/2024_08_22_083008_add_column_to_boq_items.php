<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToBoqItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('boq_items', function (Blueprint $table) {
            $table->decimal('boq_amount', 16,4)->default(0)->after('boq_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('boq_items', function (Blueprint $table) {
            $table->dropColumn('boq_amount');
        });
    }
}
