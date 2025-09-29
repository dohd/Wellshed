<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_feeds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('is_test')->default(0);
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('hash_val', 199)->nullable();
            $table->integer('trans_type')->nullable();
            $table->string('trans_id', 199)->nullable();
            $table->unsignedBigInteger('trans_time')->nullable();
            $table->decimal('trans_amount', 16,4)->default(0);
            $table->unsignedBigInteger('account_nr')->nullable();
            $table->string('narrative', 255)->nullable();
            $table->unsignedBigInteger('phone_nr')->nullable();
            $table->string('customer_name', 199)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('ft_cr_narration', 199)->nullable();
            $table->unsignedBigInteger('ins')->nullable();
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
        Schema::dropIfExists('bank_feeds');
    }
}
