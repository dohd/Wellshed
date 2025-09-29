<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNullColsOnSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            //
        });
        DB::statement('ALTER TABLE rose_suppliers MODIFY phone VARCHAR(30)');
        DB::statement('ALTER TABLE rose_suppliers MODIFY email VARCHAR(90)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            //
        });
        DB::statement('ALTER TABLE rose_suppliers MODIFY phone VARCHAR(30) NOT NULL');
        DB::statement('ALTER TABLE rose_suppliers MODIFY email VARCHAR(90) NOT NULL');
    }
}
