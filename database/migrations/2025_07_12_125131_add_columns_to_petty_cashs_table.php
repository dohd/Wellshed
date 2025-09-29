<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPettyCashsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('petty_cashs', function (Blueprint $table) {
            $table->enum('item_type',['purchase_requisition','others'])->default('others');
            $table->enum('user_type',['employee','casual','third_party_user'])->default('employee');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('casual_id')->nullable();
            $table->unsignedBigInteger('third_party_user_id')->nullable();
            $table->decimal('amount_given',22,2)->default(0);
            $table->decimal('balance',22,2)->default(0);
            $table->text('approver_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('petty_cashs', function (Blueprint $table) {
            $table->dropColumn(['item_type','user_type','employee_id','casual_id','third_party_user_id','amount_given','approver_ids','balance']);
        });
    }
}
