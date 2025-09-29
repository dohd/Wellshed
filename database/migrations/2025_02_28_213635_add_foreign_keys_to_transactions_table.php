<?php

use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('ALTER TABLE rose_transactions ADD CONSTRAINT fk_account FOREIGN KEY (account_id) REFERENCES rose_accounts (id)');
        \DB::statement('ALTER TABLE rose_transactions ADD CONSTRAINT fk_invoice FOREIGN KEY (invoice_id) REFERENCES rose_invoices (id) ON DELETE CASCADE');
        \DB::statement('ALTER TABLE rose_transactions ADD CONSTRAINT fk_bill FOREIGN KEY (bill_id) REFERENCES rose_utility_bills (id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement('ALTER TABLE rose_transactions DROP CONSTRAINT fk_account');
        \DB::statement('ALTER TABLE rose_transactions DROP CONSTRAINT fk_invoice');
        \DB::statement('ALTER TABLE rose_transactions DROP CONSTRAINT fk_bill');
    }
}
