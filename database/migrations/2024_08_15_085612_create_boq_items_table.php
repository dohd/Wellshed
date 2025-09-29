<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoqItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boq_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('boq_id');
            $table->unsignedBigInteger('product_id')->default(0);
            $table->string('numbering')->nullable();
            $table->longText('description')->nullable();
            $table->string('uom')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('qty', 16,4)->default(0);
            $table->decimal('new_qty', 16,4)->default(0);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->decimal('boq_rate', 16,4)->default(0);
            $table->decimal('tax_rate', 16,4)->default(0);
            $table->decimal('product_subtotal', 16,4)->default(0);
            $table->decimal('rate', 16,4)->default(0);
            $table->decimal('amount', 16,4)->default(0);
            $table->enum('type', ['title','product']);
            $table->unsignedBigInteger('row_index')->default(0);
            $table->unsignedBigInteger('misc')->default(0);
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id')->nullable();
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
        Schema::dropIfExists('boq_items');
    }
}
