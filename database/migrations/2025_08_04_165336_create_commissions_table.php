<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tid');
            $table->text('title')->nullable();
            $table->date('date')->nullable();
            $table->enum('status',['due','paid','partial','cancelled'])->default('due');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ins');
            $table->decimal('total', 22,2)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        try {
            DB::table('permissions')->insert([
                [
                    'name' => 'manage-commission',
                    'display_name' => 'Commission Payment Manage Permission',
                    'module_id' => 3,
                    'sort' => 0,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'create-commission',
                    'display_name' => 'Commission Payment Create Permission',
                    'module_id' => 3,
                    'sort' => 0,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'edit-commission',
                    'display_name' => 'Commission Payment Edit Permission',
                    'module_id' => 3,
                    'sort' => 0,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'delete-commission',
                    'display_name' => 'Commission Delete Permission',
                    'module_id' => 3,
                    'sort' => 0,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            // Log error to storage/logs/laravel.log
            logger()->error('Failed to insert commission permissions: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commissions');
        try {
            DB::table('rose_permissions')
                ->whereIn('name', [
                    'manage-commission',
                    'create-commission',
                    'edit-commission',
                    'delete-commission'
                ])
                ->delete();
        } catch (\Exception $e) {
            logger()->error('Failed to delete commission permissions: ' . $e->getMessage());
        }
    }
}
