<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tid');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->longText('description')->nullable();
            $table->text('route')->nullable();
            $table->enum('order_type', ['one_time', 'recurring'])->default('one_time');
            $table->string('frequency')->nullable();
            $table->enum('status',['draft','confirmed','started','completed','suspended'])->default('draft');
            // $table->enum('delivery_status', ['pending', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('tax', 16, 2)->default(0);
            $table->decimal('taxable', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ins');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_orders');
    }
}
