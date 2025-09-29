<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockIssuanceApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_issuance_approvals', function (Blueprint $table) {

            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('sia_number', 30)->primary();

            $table->string('sir_number');
            $table->foreign('sir_number')
                ->references('sir_number')
                ->on('stock_issuance_requests')
                ->onDelete('cascade');

            $table->unsignedInteger('approved_by');
            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->string('date', 80);

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
        Schema::dropIfExists('stock_issuance_approvals');
    }
}
