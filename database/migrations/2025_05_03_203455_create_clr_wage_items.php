<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClrWageItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clr_wage_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ins');
            $table->string('clr_number');
            $table->unsignedBigInteger('casual_labourer_id');
            $table->unsignedBigInteger('wage_item_id');
            $table->decimal('wage_item_total', 16, 4)->default(0);
            $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('clr_wage_items');
    }
}
