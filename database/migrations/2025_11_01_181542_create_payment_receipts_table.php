<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tid')->default(0);
            $table->timestamp('confirmed_at')->nullable();

            // Core context
            $table->enum('entry_type', ['receive', 'debit']);                  // from Entry Type
            $table->unsignedBigInteger('customer_id');    // <— adjust or add FK if you have customers table
            $table->date('date');
            $table->string('notes')->nullable();

            // Amounts
            $table->decimal('amount', 12, 2)->default(0); 
            $table->decimal('debit', 12, 2)->default(0); 
            $table->decimal('credit', 12, 2)->default(0);                                  // required in both receive & debit

            // RECEIVE specifics
            $table->enum('payment_for', ['subscription', 'order', 'charge'])->nullable();  // only when entry_type = receive
            $table->enum('payment_method', ['cash', 'mpesa'])->nullable();                 // only when entry_type = receive

            // M-Pesa refs (receive-only; nullable to allow cash)
            $table->string('mpesa_ref', 32)->nullable()->unique();             // e.g., QJK3XYZ1 (unique when present)
            $table->string('mpesa_phone', 32)->nullable();                     // optional payer phone

            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('charge_id')->nullable(); 

            $table->unsignedBigInteger('ins')->nullable(); 
            $table->unsignedBigInteger('created_by')->nullable();      // staff user id if applicable
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_receipts');
    }
}
