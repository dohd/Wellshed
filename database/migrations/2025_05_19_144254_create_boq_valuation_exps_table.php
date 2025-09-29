<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoqValuationExpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boq_valuation_exps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('boq_valuation_id');
            $table->string('product_name', 191);
            $table->decimal('amount', 16, 4)->default(0.0000);
            $table->decimal('perc_valuated', 10, 4)->default(0.0000);
            $table->decimal('total_valuated', 16, 4)->default(0.0000);
            $table->unsignedBigInteger('productvar_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('boq_id');
            $table->unsignedBigInteger('budget_item_id')->nullable();
            $table->decimal('valued_bal', 16, 4)->default(0.0000);
            $table->unsignedBigInteger('expitem_id')->nullable();
            $table->unsignedBigInteger('budget_line_id')->nullable();
            $table->string('casual_remun_id', 191)->nullable();
            $table->unsignedBigInteger('ins');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boq_valuation_exps');
    }
}
