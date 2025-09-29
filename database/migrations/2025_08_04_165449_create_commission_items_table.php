<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('commission_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('quote_id')->nullable();
            $table->string('reserve_uuid');
            $table->string('name');
            $table->string('phone');
            $table->string('commission_type')->nullable();
            $table->decimal('raw_commision',22,2)->default(0);
            $table->decimal('actual_commission',22,2)->default(0);
            $table->decimal('invoice_amount',22,2)->default(0);
            $table->decimal('quote_amount',22,2)->default(0);
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
        Schema::dropIfExists('commission_items');
    }
}
