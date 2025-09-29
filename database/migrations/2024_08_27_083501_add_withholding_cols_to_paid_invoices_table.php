<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWithholdingColsToPaidInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paid_invoices', function (Blueprint $table) {
            $table->decimal('wh_vat_amount', 16, 4)->default(0)->after('allocate_ttl');
            $table->decimal('wh_tax_amount', 16, 4)->default(0)->after('wh_vat_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paid_invoices', function (Blueprint $table) {
            $table->dropColumn(['wh_vat_amount', 'wh_tax_amount']);
        });
    }
}
