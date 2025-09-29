<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {

            $table->string('account_no')->after('on_account');
            $table->string('account_name')->after('account_no');
            $table->string('bank')->after('account_name');
            $table->string('bank_code')->after('bank');
            $table->string('payment_terms')->after('bank_code');
            $table->decimal('credit_limit', 16,4)->after('payment_terms');
            $table->string('mpesa_payment')->after('credit_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
}
