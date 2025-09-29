<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTenantPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_packages', function (Blueprint $table) {
            $table->integer('no_of_users')->nullable();
            $table->decimal('subscription_rate',16,4)->default(0);
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
            $table->dropColumn(['no_of_users','subscription_rate']);
        });
    }
}
