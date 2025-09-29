<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportRequestExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_request_expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('import_request_id');
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->decimal('exp_qty', 22,2)->default(0);
            $table->decimal('exp_rate', 22,2)->default(0);
            $table->decimal('fx_curr_rate', 22,2)->default(0);
            $table->decimal('fx_rate', 22,2)->default(0);
            $table->string('uom')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('ins')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_request_expenses');
    }
}
