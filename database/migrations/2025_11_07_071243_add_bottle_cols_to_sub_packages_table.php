<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBottleColsToSubPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sub_packages', function (Blueprint $table) {
            $table->unsignedInteger('max_bottle')->default(0);
            $table->unsignedBigInteger('productvar_id')->nullable();

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sub_packages', function (Blueprint $table) {
            $table->dropColumn(['max_bottle', 'productvar_id', 'updated_by', 'deleted_by', 'deleted_at']);
        });
    }
}
