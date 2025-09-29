<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credit_note_id')->nullable();
            $table->unsignedBigInteger('invoice_item_id')->nullable();
            $table->unsignedBigInteger('productvar_id')->nullable();
            $table->string('cstm_project_type', 50)->nullable();
            $table->string('numbering', 5)->nullable();
            $table->text('name')->nullable();
            $table->string('unit', 5)->nullable();
            $table->decimal('qty', 16, 4)->default(0);
            $table->decimal('rate', 16, 4)->default(0);
            $table->decimal('subtotal', 16, 4)->default(0);
            $table->decimal('taxable', 16, 4)->default(0);
            $table->decimal('total', 16, 4)->default(0);
            $table->decimal('tax', 16, 4)->default(0);
            $table->decimal('tax_id', 10, 2)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
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
        Schema::dropIfExists('credit_note_items');
    }
}
