<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToEdlSubcategoryAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('edl_subcategory_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('allocations');
            $table->unsignedBigInteger('branch_id')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('edl_subcategory_allocations', function (Blueprint $table) {
            $table->dropColumn(['customer_id','branch_id']);
        });
    }
}
