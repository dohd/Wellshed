<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthsColumnsToPurchaseClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_class_budgets', function (Blueprint $table) {
            $months = [
                'december', 'november', 'october', 'september', 'august', 'july',
                'june', 'may', 'april', 'march', 'february', 'january'
            ];

            foreach ($months as $month) {
                $table->decimal($month, 20, 2)->after('financial_year_id')->default(0.00);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_class_budgets', function (Blueprint $table) {
            $months = [
                'december', 'november', 'october', 'september', 'august', 'july',
                'june', 'may', 'april', 'march', 'february', 'january'
            ];

            foreach ($months as $month) {
                $table->dropColumn($month);
            }
        });
    }
}