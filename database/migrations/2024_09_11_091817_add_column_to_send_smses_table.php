<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToSendSmsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('send_smses', function (Blueprint $table) {
            $table->decimal('characters', 8,2)->defaultValue(0)->after('sent_to_ids');
            $table->decimal('cost', 8,2)->defaultValue(0)->after('characters');
            $table->decimal('user_count', 8,2)->defaultValue(0)->after('cost');
            $table->decimal('total_cost', 8,2)->defaultValue(0)->after('user_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('send_smses', function (Blueprint $table) {
            $table->dropColumn(['characters','cost','user_count','total_cost']);
        });
    }
}
