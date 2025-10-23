<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStkPushTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stk_push', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Request metadata
            $table->string('merchant_request_id')->nullable();
            $table->string('checkout_request_id')->nullable()->index();
            $table->string('account_reference')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedInteger('amount')->default(0);

            // Callback / result fields
            $table->string('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->string('mpesa_receipt_number')->nullable()->index();
            $table->timestamp('paid_at')->nullable(); // derived from TransactionDate
            $table->json('raw_callback')->nullable();

            // Status: PENDING, SUCCESS, FAILED, CANCELLED, TIMEOUT, ERROR
            $table->string('status', 20)->default('PENDING')->index();
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
        Schema::dropIfExists('stk_push');
    }
}
