<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToPurchaseRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->enum('item_type',['stock','project','others'])->default('stock')->after('employee_id');
            $table->unsignedBigInteger('project_id')->nullable()->after('item_type');
            $table->unsignedBigInteger('project_milestone_id')->nullable()->after('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['project_id', 'project_milestone_id','item_type']);
        });
    }
}
