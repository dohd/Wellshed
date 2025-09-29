<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToSellPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sell_prices', function (Blueprint $table) {
            $table->enum('recommend_type', ['pending', 'percentage','fixed'])->default('fixed');
            $table->decimal('recommended_value', 22,2)->default(0);
            $table->enum('status', ['pending','approved','amend','rejected'])->default('pending');
            $table->text('status_note')->nullable();
            $table->date('approval_date')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sell_prices', function (Blueprint $table) {
            $table->dropColumn(['recommend_type','recommended_value','status','status_note','approval_date','approved_by']);
        });
    }
}
