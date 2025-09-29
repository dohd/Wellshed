<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToClientVendorTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_vendor_tickets', function (Blueprint $table) {
            $table->bigInteger('quote_id')->nullable();
            $table->bigInteger('project_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_vendor_tickets', function (Blueprint $table) {
            $table->dropColumn(['quote_id', 'project_id']);
        });
    }
}
