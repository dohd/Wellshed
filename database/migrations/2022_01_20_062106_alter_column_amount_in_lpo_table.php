<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnAmountInLpoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lpo', function (Blueprint $table) {
            //
        });
        DB::statement('ALTER TABLE rose_lpos MODIFY COLUMN amount DECIMAL(16, 2)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lpo', function (Blueprint $table) {
            //
        });
    }
}
