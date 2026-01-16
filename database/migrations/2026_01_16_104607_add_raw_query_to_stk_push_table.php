<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRawQueryToStkPushTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stk_push', function (Blueprint $table) {
            $table->json('raw_query')->nullable()->after('raw_callback');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stk_push', function (Blueprint $table) {
            $table->dropColumn('raw_query');
        });
    }
}
