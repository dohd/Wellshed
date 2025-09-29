<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobValuationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_valuations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tid')->default(0);
            $table->date('date')->nullable();
            $table->bigInteger('quote_id')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->string('note', 255)->nullable();
            $table->integer('tax_id')->default(0);
            $table->decimal('taxable', 16, 4)->default(0);
            $table->decimal('subtotal', 16, 4)->default(0);
            $table->decimal('tax', 16, 4)->default(0);
            $table->decimal('total', 16, 4)->default(0);
            $table->decimal('balance', 16, 4)->default(0);
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('ins')->nullable();
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
        Schema::dropIfExists('job_valuations');
    }
}
