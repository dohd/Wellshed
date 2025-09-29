<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpenseCategoryColumnToPurchaseClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_classes', function (Blueprint $table) {

            $table->unsignedBigInteger('expense_category')->nullable()->after('name');
            $table->foreign('expense_category')->references('id')->on('expense_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_classes', function (Blueprint $table) {
            //
        });
    }
}
