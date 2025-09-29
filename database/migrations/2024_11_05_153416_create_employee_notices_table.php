<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_notices', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->text('title');

            $table->date('date');

            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('users');

            $table->text('content')->nullable();

            $table->text('document_path')->nullable();

            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');

            $table->timestamps();


            DB::table('permissions')->insert([
                [
                    'name' => 'manage-employee-notice',
                    'display_name' => 'Employee Notice Manage Permission',
                    'module_id' => 19,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'create-employee-notice',
                    'display_name' => 'Employee Notice Create Permission',
                    'module_id' => 19,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'view-employee-notice',
                    'display_name' => 'Employee Notice View Permission',
                    'module_id' => 19,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'edit-employee-notice',
                    'display_name' => 'Employee Notice Edit Permission',
                    'module_id' => 19,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'delete-employee-notice',
                    'display_name' => 'Employee Notice Delete Permission',
                    'module_id' => 19,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_notices');
    }
}
