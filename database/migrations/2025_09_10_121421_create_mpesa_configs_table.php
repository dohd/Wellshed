<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpesaConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('env', ['sandbox', 'production'])->default('sandbox');
            $table->enum('type', ['b2c', 'c2b_store', 'c2b_paybill', 'stk_push'])->default('c2b_paybill');

            $table->string('base_url')->nullable();
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->string('shortcode');                       // PayBill or Till
            $table->string('head_office_shortcode')->nullable(); // For PayBill hierarchy

            // B2C specific
            $table->string('initiator_name')->nullable();
            $table->text('initiator_password_enc')->nullable();  // encrypted (Laravel Crypt)
            // $table->string('security_credential')->nullable();   // generated using Safaricom cert
            // $table->string('command_id')->nullable();            // e.g. BusinessPayment, SalaryPayment
            $table->string('result_url')->nullable();            // where B2C results are posted
            $table->string('timeout_url')->nullable();           // where B2C timeout notifications go

            // C2B specific
            $table->string('validation_url')->nullable();        // for real-time validation
            $table->string('confirmation_url')->nullable();      // where confirmed payments are sent

            // STK Push / Online Checkout
            $table->string('passkey')->nullable();               // STK Push passkey
            $table->string('account_reference')->nullable();     // default account reference
            $table->string('callback_url')->nullable();          // callback for STK Push

            // Certificates (used in B2C/B2B encryption)
            $table->string('cert_path')->nullable();             // file path to Safaricom cert

            // Token caching
            $table->text('last_token')->nullable();
            $table->timestamp('last_token_expires_at')->nullable();

            // Audit
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
        Schema::dropIfExists('mpesa_configs');
    }
}
