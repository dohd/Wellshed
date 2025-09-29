<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEfrisGoodsCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('efris_goods_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('segment_name');
            $table->unsignedBigInteger('family_code');
            $table->string('family_name');
            $table->unsignedBigInteger('class_code');
            $table->string('class_name');
            $table->unsignedBigInteger('commodity_code');
            $table->string('commodity_name');
            $table->string('is_service',20);
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
        Schema::dropIfExists('efris_goods_categories');
    }
}
