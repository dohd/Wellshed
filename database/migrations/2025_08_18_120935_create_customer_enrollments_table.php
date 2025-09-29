<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerEnrollmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_enrollments', function (Blueprint $table) {
            $table->bigIncrements('id');
             // Client fields
            $table->enum('client_status', ['customer', 'new'])->default('customer');
            $table->unsignedBigInteger('client_id')->nullable(); // For existing customers
            $table->string('name')->nullable();                  // For new client
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('dob')->nullable();

            // Promo fields
            $table->string('redeemable_code')->unique();
            $table->unsignedBigInteger('promo_code_id')->nullable();
            $table->string('reserve_uuid')->nullable();
            $table->string('promo_type')->nullable();
            $table->text('description_promo')->nullable();
            $table->enum('payment_status',['pending','partial','paid'])->default('pending');

            // New: store multiple IDs as CSV
            $table->text('product_categories')->nullable(); // will hold comma-separated category IDs
            $table->text('products')->nullable();           // will hold comma-separated product IDs

            $table->text('note')->nullable();
            $table->enum('status',['pending', 'review', 'approved', 'rejected'])->default('pending');
            $table->enum('notification_status',['no', 'yes'])->default('no');
            $table->date('date')->nullable();
            $table->decimal('quote_amount', 22,2)->default(0);
            $table->text('status_note')->nullable();
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
        Schema::dropIfExists('customer_enrollments');
    }
}
