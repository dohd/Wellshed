<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobValuationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_valuation_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_valuation_id')->nullable();
            $table->bigInteger('productvar_id')->nullable();
            $table->bigInteger('verified_item_id')->nullable();
            $table->integer('row_type')->default(1);
            $table->integer('row_index')->default(0);
            $table->decimal('perc_valuated', 8, 4)->default(0);
            $table->decimal('total_valuated', 16, 4)->default(0);
            $table->string('numbering', 10)->nullable();
            $table->string('product_name', 255)->nullable();
            $table->string('unit', 10)->nullable();
            $table->decimal('product_qty', 16, 4)->default(0);
            $table->decimal('tax_rate', 16, 4)->default(0);
            $table->decimal('product_subtotal', 16, 4)->default(0);
            $table->decimal('product_price', 16, 4)->default(0);
            $table->decimal('product_tax', 16, 4)->default(0);
            $table->decimal('product_amount', 16, 4)->default(0);
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
        Schema::dropIfExists('job_valuation_items');
    }
}
