<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeComplaintFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify columns using raw SQL
        DB::statement('ALTER TABLE rose_customer_complains MODIFY COLUMN customer_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE rose_customer_complains MODIFY COLUMN project_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE rose_customer_complains MODIFY COLUMN user_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE rose_customer_complains MODIFY COLUMN employees VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
