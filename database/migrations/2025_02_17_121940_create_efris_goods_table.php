<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEfrisGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('efris_goods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('productvar_id');
            $table->unsignedBigInteger('commodity_category_id');
            $table->string('goods_name');
            $table->string('goods_code');
            $table->string('measure_unit',20);
            $table->decimal('unit_price', 16, 4)->default(0);
            $table->string('currency',20);
            $table->decimal('stock_prewarning', 16, 4)->default(0);
            $table->string('have_excise_tax',20);
            $table->string('have_piece_unit',20);
            $table->decimal('piece_unit_price', 16, 4)->default(0);
            $table->string('piece_measure_unit', 20);
            $table->string('package_scaled_value', 20)->nullable();
            $table->string('piece_scaled_value', 20)->nullable();
            $table->unsignedBigInteger('ins');
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
        Schema::dropIfExists('efris_goods');
    }
}
