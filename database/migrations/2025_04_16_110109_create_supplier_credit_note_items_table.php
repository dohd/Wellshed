<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierCreditNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_credit_note_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_creditnote_id');
            $table->string('numbering')->nullable();
            $table->string('cstm_project_type')->nullable();
            $table->string('name')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('prod_taxid', 15, 2)->nullable();
            $table->decimal('prod_tax', 15, 2)->default(0);
            $table->decimal('prod_total', 15, 2)->default(0);
            $table->decimal('prod_subtotal', 15, 2)->default(0);
            $table->decimal('prod_taxable', 15, 2)->default(0);
            $table->unsignedBigInteger('productvar_id')->nullable();
            $table->unsignedBigInteger('bill_item_id')->nullable();
            $table->decimal('prod_fx_rate', 15, 4)->default(0);
            $table->decimal('prod_fx_taxable', 15, 2)->default(0);
            $table->decimal('prod_fx_subtotal', 15, 2)->default(0);
            $table->decimal('prod_fx_tax', 15, 2)->default(0);
            $table->decimal('prod_fx_total', 15, 2)->default(0);
            $table->decimal('prod_fx_gain', 15, 2)->default(0);
            $table->decimal('prod_fx_loss', 15, 2)->default(0);
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
        Schema::dropIfExists('supplier_credit_note_items');
    }
}
