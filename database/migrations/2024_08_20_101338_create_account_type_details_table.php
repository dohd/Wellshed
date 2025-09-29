<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTypeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_type_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 199);
            $table->string('category', 199);
            $table->text('description')->nullable();
            $table->string('system_rel', 199)->nullable();
            $table->string('system', 199);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_type_details');
    }
}
