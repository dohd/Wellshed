<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandardTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standard_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tid');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        DB::table('permissions')->insert([
            [
                'name' => 'manage-standard_template',
                'display_name' => 'Standard Template Manage Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'create-standard_template',
                'display_name' => 'Standard Template Create Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'edit-standard_template',
                'display_name' => 'Standard Template Edit Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'delete-standard_template',
                'display_name' => 'Standard Template Delete Permission',
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
        Schema::dropIfExists('standard_templates');
    }
}
