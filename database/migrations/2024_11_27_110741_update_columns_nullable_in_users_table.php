<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsNullableInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `rose_users` MODIFY COLUMN `is_term_accept` TINYINT(1) NULL");
        DB::statement("ALTER TABLE `rose_users` MODIFY COLUMN `confirmed` TINYINT(1) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `rose_users` MODIFY COLUMN `is_term_accept` TINYINT(1) NOT NULL");
        DB::statement("ALTER TABLE `rose_users` MODIFY COLUMN `confirmed` TINYINT(1) NOT NULL");
    }
}