<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantServicePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_service_packages', function (Blueprint $table) {

            $table->string('tsp_number')->primary();

            $table->bigInteger('tenant_service_id')->unique();
            $table->foreign('tenant_service_id')->references('id')->on('tenant_services');

            $table->string('package_number');
            $table->foreign('package_number')->references('package_number')->on('packages');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_service_packages');
    }
}
