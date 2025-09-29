<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertKeyActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO rose_key_activities (name, description, ins)
            SELECT DISTINCT key_activities, key_activities, ins
            FROM rose_employee_task_subcategories 
            WHERE key_activities IS NOT NULL
        ");

        DB::statement("
            UPDATE rose_employee_task_subcategories es
            JOIN rose_key_activities ka
            ON ka.name COLLATE utf8mb4_general_ci = es.key_activities COLLATE utf8mb4_general_ci
            SET es.key_activity_id = ka.id;
        ");
        DB::table('permissions')->insert([
            [
                'name' => 'manage-key_activity',
                'display_name' => 'Key Activity Manage Permission',
                'module_id' => 15,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null
            ],
            [
                'name' => 'create-key_activity',
                'display_name' => 'Key Activity Create Permission',
                'module_id' => 15,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null
            ],
            [
                'name' => 'edit-key_activity',
                'display_name' => 'Key Activity Edit Permission',
                'module_id' => 15,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null
            ],
            [
                'name' => 'delete-key_activity',
                'display_name' => 'Key Activity Delete Permission',
                'module_id' => 15,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null
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
        DB::table('rose_key_activities')
        ->whereIn('name', function ($query) {
            $query->select('key_activities')
                  ->from('rose_employee_task_subcategories')
                  ->whereNotNull('key_activities');
        })
        ->delete();
        DB::statement("
            UPDATE rose_employee_task_subcategories
            SET key_activity_id = NULL
            WHERE key_activity_id IS NOT NULL;
        ");
        DB::table('permissions')
            ->whereIn('name', [
                'manage-key_activity',
                'create-key_activity',
                'edit-key_activity',
                'delete-key_activity'
            ])
            ->delete();
    }
}
