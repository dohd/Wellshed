<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoqValuationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boq_valuations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ins')->nullable();
            $table->unsignedBigInteger('tid')->default(0);
            $table->date('date')->nullable();
            $table->unsignedBigInteger('boq_id')->nullable();
            $table->unsignedBigInteger('quote_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('note')->nullable();
            $table->unsignedInteger('tax_id')->default(0);
            $table->decimal('taxable', 16, 4)->default(0.0000);
            $table->decimal('subtotal', 16, 4)->default(0.0000);
            $table->decimal('tax', 16, 4)->default(0.0000);
            $table->decimal('total', 16, 4)->default(0.0000);
            $table->decimal('balance', 16, 4)->default(0.0000);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('exp_total', 16, 4)->default(0.0000);
            $table->decimal('exp_valuated', 16, 4)->default(0.0000);
            $table->decimal('exp_balance', 16, 4)->default(0.0000);
            $table->decimal('exp_valuated_perc', 10, 4)->default(0.0000);
            $table->decimal('valued_taxable', 16, 4)->default(0.0000);
            $table->decimal('valued_subtotal', 16, 4)->default(0.0000);
            $table->decimal('valued_tax', 16, 4)->default(0.0000);
            $table->decimal('valued_total', 16, 4)->default(0.0000);
            $table->decimal('valued_perc', 10, 4)->default(0.0000);
            $table->integer('is_final')->nullable();
            $table->string('completion_cert', 191)->nullable();
            $table->decimal('perc_retention', 10, 4)->default(0.0000);
            $table->decimal('retention', 16, 4)->default(0.0000);
            $table->date('completion_date')->nullable();
            $table->decimal('dlp_period', 8, 2)->nullable();
            $table->decimal('dlp_reminder', 8, 2)->nullable();
            $table->text('employee_ids')->nullable();
            $table->string('retention_note', 191)->nullable();
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
        Schema::dropIfExists('boq_valuations');
    }
}
