<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWageItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wage_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ins');
            $table->enum('earning_type', ['regular_pay', 'overtime', 'bonus', 'allowance', 'misc']);
            $table->string('name');
            $table->decimal('weekday_ot', 10, 2)->default(0);
            $table->decimal('weekend_sat_ot', 10, 2)->default(0);
            $table->decimal('weekend_sun_ot', 10, 2)->default(0);
            $table->decimal('holiday_ot', 10, 2)->default(0);            
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
        Schema::dropIfExists('wage_items');
    }
}
