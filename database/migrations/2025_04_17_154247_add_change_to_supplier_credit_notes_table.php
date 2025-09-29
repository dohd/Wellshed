<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangeToSupplierCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplier_credit_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('bill_id')->nullable()->change();
            $table->unsignedBigInteger('account_id')->nullable()->change();
            $table->string('payment_mode')->nullable()->change();
            $table->unsignedBigInteger('grn_id')->nullable()->after('bill_id');
            $table->enum('grn_type', ['pending','grn_invoiced','grn_not_invoiced','vendor_credit'])->default('pending')->after('grn_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplier_credit_notes', function (Blueprint $table) {
            //
        });
    }
}
