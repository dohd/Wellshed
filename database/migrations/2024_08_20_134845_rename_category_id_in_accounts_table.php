<?php

use Illuminate\Database\Migrations\Migration;

class RenameCategoryIdInAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE rose_accounts RENAME COLUMN category_id TO parent_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE rose_accounts RENAME COLUMN parent_id TO category_id');
    }
}
