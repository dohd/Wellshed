<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_credit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('is_debit')->default(0);
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tid');
            $table->date('date');
            $table->unsignedBigInteger('classlist_id');
            $table->decimal('tax_id', 10, 2);
            $table->unsignedBigInteger('bill_id');
            $table->string('cu_invoice_no')->nullable();
            $table->unsignedBigInteger('currency_id');
            $table->decimal('fx_curr_rate', 15, 4)->default(1);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('account_id');
            $table->string('payment_mode');
            $table->string('reference_no')->nullable();
            $table->string('load_items_from')->nullable();
            $table->decimal('taxable', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('fx_subtotal', 15, 2)->default(0);
            $table->decimal('fx_taxable', 15, 2)->default(0);
            $table->decimal('fx_tax', 15, 2)->default(0);
            $table->decimal('fx_total', 15, 2)->default(0);
            $table->decimal('fx_gain', 15, 2)->default(0);
            $table->decimal('fx_loss', 15, 2)->default(0);
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
        Schema::dropIfExists('supplier_credit_notes');
    }
}
