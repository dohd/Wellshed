<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportRequestItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_request_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('import_request_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->text('product_name')->nullable();
            $table->decimal('qty', 16,4)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('rate', 22, 2)->default(0);
            $table->decimal('amount', 20, 2)->default(0);
            $table->decimal('cbm', 22, 5)->default(0);
            $table->decimal('total_cbm', 22, 5)->default(0);
            $table->decimal('cbm_percent', 10, 2)->default(0);
            $table->decimal('cbm_value', 22, 2)->default(0);
            $table->decimal('rate_percent', 10, 2)->default(0);
            $table->decimal('rate_value', 22, 2)->default(0);
            $table->decimal('avg_cbm_rate_value', 22, 2)->default(0);
            $table->decimal('avg_rate_shippment', 22, 2)->default(0);
            $table->decimal('avg_rate_shippment_per_item', 22, 2)->default(0);
            $table->unsignedBigInteger('ins')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_request_items');
    }
}
