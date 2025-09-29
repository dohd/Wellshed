<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLpoExpenseIdToImportRequestExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_request_expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('lpo_expense_id')->nullable()->after('expense_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_request_expenses', function (Blueprint $table) {
            $table->dropColumn('lpo_expense_id');
        });
    }
}
