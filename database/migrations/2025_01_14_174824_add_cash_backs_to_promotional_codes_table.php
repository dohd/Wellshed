<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCashBacksToPromotionalCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotional_codes', function (Blueprint $table) {

            DB::statement("ALTER TABLE rose_promotional_codes MODIFY discount_value DECIMAL(8, 2) DEFAULT 0.00 NOT NULL");

            $table->decimal('cash_back_1', 8, 2)->default(0.00)->after('status'); // Discount amount (e.g., $10.00 or 10.00%)
            $table->decimal('cash_back_2', 8, 2)->default(0.00)->after('cash_back_1'); // Discount amount (e.g., $10.00 or 10.00%)
            $table->decimal('cash_back_3', 8, 2)->default(0.00)->after('cash_back_2'); // Discount amount (e.g., $10.00 or 10.00%)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promotional_codes', function (Blueprint $table) {

            $table->dropColumn('cash_back_1');
            $table->dropColumn('cash_back_2');
            $table->dropColumn('cash_back_3');

            DB::statement("ALTER TABLE rose_promotional_codes MODIFY discount_value DECIMAL(8, 2) NOT NULL");
        });
    }
}
