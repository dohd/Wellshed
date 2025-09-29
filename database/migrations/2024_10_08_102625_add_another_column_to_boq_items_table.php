<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnotherColumnToBoqItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('boq_items', function (Blueprint $table) {
            $table->text('product_name')->nullable()->after('description');
            // $table->integer('is_imported')->default(0);
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
            $table->dropColumn(['is_imported','product_name']);
        });
    }
}
