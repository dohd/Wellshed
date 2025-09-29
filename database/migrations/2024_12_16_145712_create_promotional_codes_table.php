<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionalCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('promotional_codes', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->string('code');

            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies');

            $table->text('description');

            $table->unsignedInteger('usage_limit'); // Maximum times this promo code can be used


            $table->unsignedInteger('reservation_period')->default(7);
            $table->unsignedInteger('reservations_count')->default(0);

            $table->unsignedInteger('res_limit_1')->default(0);
            $table->unsignedInteger('res_limit_2')->default(0);
            $table->unsignedInteger('res_limit_3')->default(0);

            $table->unsignedInteger('used_count')->default(0); // Tracks how many times it has been used

            $table->enum('promo_type', ['specific_products', 'product_categories'])->default('product_categories'); // Fixed amount or percentage discount

            $table->enum('discount_type', ['fixed', 'percentage'])->default('percentage'); // Fixed amount or percentage discount

            $table->decimal('discount_value', 8, 2); // Discount amount (e.g., $10.00 or 10.00%)
            $table->decimal('discount_value_2', 8, 2)->default(0.00); // Discount amount (e.g., $10.00 or 10.00%)
            $table->decimal('discount_value_3', 8, 2)->default(0.00); // Discount amount (e.g., $10.00 or 10.00%)

            $table->dateTime('valid_from'); // Start date of promo code validity
            $table->dateTime('valid_until'); // Expiration date

            $table->boolean('status')->default(false); // Status of the promo code

            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

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
        Schema::dropIfExists('promotional_codes');
    }
}
