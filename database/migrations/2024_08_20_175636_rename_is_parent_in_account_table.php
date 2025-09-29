<?php

use Illuminate\Database\Migrations\Migration;

class RenameIsParentInAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE rose_accounts RENAME COLUMN is_parent TO is_sub_account');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE rose_accounts RENAME COLUMN is_sub_account TO is_parent');
    }
}
