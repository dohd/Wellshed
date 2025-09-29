<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobValuationExps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_valuation_exps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ins');
            $table->bigInteger('job_valuation_id');
            $table->string('product_name');
            $table->decimal('amount', 16,4)->default(0);
            $table->decimal('perc_valuated', 10,4)->default(0);
            $table->decimal('total_valuated', 16,4)->default(0);
            $table->bigInteger('productvar_id');
            $table->bigInteger('project_id');
            $table->bigInteger('quote_id');
            $table->bigInteger('budget_item_id');
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
        Schema::dropIfExists('job_valuation_exps');
    }
}
