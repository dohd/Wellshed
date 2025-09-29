<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoqSheetIdToBomItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bom_items', function (Blueprint $table) {
            $table->unsignedBigInteger('boq_sheet_id')->nullable()->after('bom_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bom_items', function (Blueprint $table) {
            $table->dropColumn('boq_sheet_id');
        });
    }
}
