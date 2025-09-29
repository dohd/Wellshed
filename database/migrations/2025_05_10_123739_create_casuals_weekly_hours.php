<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasualsWeeklyHours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('casual_weekly_hours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('labour_allocation_id');
            $table->unsignedBigInteger('casual_labourer_id');
            $table->integer('is_overtime')->nullable();
            $table->decimal('sun', 10, 2)->default(0);
            $table->decimal('mon', 10, 2)->default(0);
            $table->decimal('tue', 10, 2)->default(0);
            $table->decimal('wed', 10, 2)->default(0);
            $table->decimal('thu', 10, 2)->default(0);
            $table->decimal('fri', 10, 2)->default(0);
            $table->decimal('sat', 10, 2)->default(0);
            $table->decimal('total_reg_hrs', 10, 2)->default(0);
            $table->decimal('total_ot_hrs', 10, 2)->default(0);
            $table->decimal('total_hrs', 10, 2)->default(0);
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
        Schema::dropIfExists('casuals_weekly_hours');
    }
}
