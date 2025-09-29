<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetRequisitionTypeToNullableInPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("ALTER TABLE rose_purchase_orders MODIFY COLUMN requisition_type ENUM('rfq', 'purchase_requisition', 'pending') NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("ALTER TABLE rose_purchase_orders MODIFY COLUMN requisition_type ENUM('rfq', 'purchase_requisition', 'pending') DEFAULT 'pending'");
    }
}
