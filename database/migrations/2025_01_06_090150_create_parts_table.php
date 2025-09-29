<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('product_id')->nulluble();
            $table->unsignedBigInteger('template_id')->nulluble();
            $table->enum('type',['no','yes'])->default('no');
            $table->string('note')->nulluble();
            $table->decimal('total_qty',16,4)->default(0);
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        DB::table('permissions')->insert([
            [
                'name' => 'manage-part',
                'display_name' => 'Part Manage Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'create-part',
                'display_name' => 'Part Create Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'edit-part',
                'display_name' => 'Part Edit Permission',
                'module_id' => 3,
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'delete-part',
                'display_name' => 'Part Delete Permission',
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
        Schema::dropIfExists('parts');
    }
}
