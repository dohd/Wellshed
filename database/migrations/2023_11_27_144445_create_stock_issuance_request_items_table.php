<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockIssuanceRequestItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_issuance_request_items', function (Blueprint $table) {

            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('siri_number', 30)->primary();

            $table->string('sir_number');
            $table->foreign('sir_number')
                ->references('sir_number')
                ->on('stock_issuance_requests')
                ->onDelete('cascade');

            $table->unsignedInteger('product');
            $table->decimal('quantity', 16, 2);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_issuance_request_items');
    }
}
