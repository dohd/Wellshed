<?php

use Illuminate\Database\Migrations\Migration;

class RenameRateColsInBankTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE rose_bank_transfers RENAME COLUMN dest_rate to default_rate');
        DB::statement('ALTER TABLE rose_bank_transfers RENAME COLUMN source_rate to bank_rate');
        DB::statement('ALTER TABLE rose_bank_transfers ADD COLUMN receipt_amount DECIMAL(16,4) DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE rose_bank_transfers RENAME COLUMN default_rate to dest_rate');
        DB::statement('ALTER TABLE rose_bank_transfers RENAME COLUMN bank_rate to source_rate');
        DB::statement('ALTER TABLE rose_bank_transfers DROP COLUMN receipt_amount');
    }
}
