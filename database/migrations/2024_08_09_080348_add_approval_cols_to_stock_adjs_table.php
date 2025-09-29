<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalColsToStockAdjsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_adjs', function (Blueprint $table) {
            $table->enum('approval_status', ['Pending', 'Approved'])->default('Pending')->after('id');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_adjs', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approved_by']);
        });
    }
}
