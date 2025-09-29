<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToStockIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_issues', function (Blueprint $table) {
            $table->unsignedInteger('approved_by')->nullable(); // Replace 'existing_column' with the column after which you want to add 'approved_by'
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->text('approval_note')->nullable()->after('approved_by');
            $table->enum('status', ['APPROVED', 'ON HOLD', 'REJECTED', 'PENDING'])->default('PENDING')->after('approval_note');// Replace 'another_existing_column' with the column after which you want to add 'status'
            $table->string('issue_to_third_party')->nullable()->after('issue_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_issues', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');
            $table->dropColumn('status');
        });
    }
}