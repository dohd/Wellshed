<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerEnrollmentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_enrollment_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_enrollment_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('redeemable_code')->nullable();

            // Foreign key to promo_codes table
            $table->unsignedBigInteger('promo_code_id')->nullable();

            $table->uuid('reservation_uuid')->nullable();

            $table->decimal('raw_commission', 15, 2)->default(0);
            $table->decimal('actual_commission', 15, 2)->default(0);
            $table->string('commission')->nullable();
            $table->enum('payment_status',['paid','not_paid'])->default('not_paid');
            $table->date('payment_date')->nullable();
            $table->string('commission_type')->nullable();
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id');

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
        Schema::dropIfExists('customer_enrollment_items');
    }
}
