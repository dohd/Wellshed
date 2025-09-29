<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePromotionalDiscountDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_promotional_discount_data', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('product_variations')->onDelete('cascade');

            $table->string('name');
            $table->string('discount_type');
            $table->decimal('discount_offered', 8, 2); // Assuming percentage stored as a decimal
            $table->decimal('price', 15, 2);
            $table->decimal('unit_discount', 15, 2);
            $table->integer('quantity');
            $table->decimal('discount', 15, 2);
            $table->decimal('tax_rate', 5, 2); // Assuming tax rate is stored as a percentage
            $table->decimal('discounted_tax', 15, 2);

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
        Schema::dropIfExists('invoice_promotional_discount_data');
    }
}
