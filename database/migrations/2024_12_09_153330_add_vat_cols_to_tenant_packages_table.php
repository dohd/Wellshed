<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatColsToTenantPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_packages', function (Blueprint $table) {
            $table->decimal('vat_rate', 10,2)->default(0);
            $table->decimal('vat', 16,4)->default(0);
            $table->decimal('net_cost', 16,4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenant_packages', function (Blueprint $table) {
            $table->dropColumn(['vat_rate', 'vat', 'net_cost']);
        });
    }
}
