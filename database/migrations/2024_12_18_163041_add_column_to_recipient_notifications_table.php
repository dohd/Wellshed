<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToRecipientNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recipient_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->nullable()->change();
            $table->unsignedBigInteger('milestone_id')->nullable()->after('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recipient_notifications', function (Blueprint $table) {
            $table->dropColumn('milestone_id');
        });
    }
}
