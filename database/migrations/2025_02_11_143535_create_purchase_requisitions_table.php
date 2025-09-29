<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequisitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tid');
            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->enum('status', ['pending', 'approved', 'rejected','amend'])->default('pending'); // Adjust values as needed
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium'); // Adjust values as needed
            $table->date('expect_date')->nullable();
            $table->text('note')->nullable();
            $table->text('status_note')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('project_milestone_id')->nullable();
            $table->string('item_type');
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        DB::table('permissions')->insert([
            [
                'name' => 'delete-purchase_requisition',
                'display_name' => 'Purchases Requisition Delete Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'edit-purchase_requisition',
                'display_name' => 'Purchases Requisition Edit Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'create-purchase_requisition',
                'display_name' => 'Purchases Requisition Create Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'manage-purchase_requisition',
                'display_name' => 'Purchases Requisition Manage Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_requisitions');
    }
}
