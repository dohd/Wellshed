<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('tid');
            $table->date('date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->decimal('fx_curr_rate', 16,4)->default(0);
            $table->text('purchase_requisition_ids')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('shipping_cost',22,2)->default(0);
            $table->decimal('item_cost',22,2)->default(0);
            $table->decimal('total', 22,2)->default(0);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ins');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        DB::table('permissions')->insert([
            [
                'name' => 'manage-import_request',
                'display_name' => 'Import Request Manage Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'create-import_request',
                'display_name' => 'Import Request Create Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'edit-import_request',
                'display_name' => 'Import Request Edit Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'delete-import_request',
                'display_name' => 'Import Request Delete Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
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
        Schema::dropIfExists('import_requests');
    }
}
