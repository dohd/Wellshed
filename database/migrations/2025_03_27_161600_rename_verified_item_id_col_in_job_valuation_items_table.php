<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameVerifiedItemIdColInJobValuationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_valuation_items', function (Blueprint $table) {
            $table->renameColumn('verified_item_id', 'quote_item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_valuation_items', function (Blueprint $table) {
            $table->renameColumn('verified_item_id', 'quote_item_id');
        });
    }
}
