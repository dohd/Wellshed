<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigratePurchaseClassData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Step 1: Insert unique (name, ins) combinations into purchase_classes
        $budgets = DB::table('purchase_class_budgets')->select('name', 'ins')->distinct()->get();

        foreach ($budgets as $budget) {
            DB::table('purchase_classes')->updateOrInsert(
                ['name' => $budget->name, 'ins' => $budget->ins ?? 1]
            );
        }

        // Step 2: Add purchase_class_id to purchase_class_budgets
        Schema::table('purchase_class_budgets', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_class_id')->nullable()->after('name');
        });

        // Step 3: Update purchase_class_id with corresponding IDs
        $budgets = DB::table('purchase_class_budgets')->get();

        foreach ($budgets as $budget) {
            $classId = DB::table('purchase_classes')
                ->where('name', $budget->name)
                ->where('ins', $budget->ins)
                ->value('id');

            DB::table('purchase_class_budgets')
                ->where('id', $budget->id)
                ->update(['purchase_class_id' => $classId]);
        }

        // Step 4: Drop the name column from purchase_class_budgets
        Schema::table('purchase_class_budgets', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        // Step 5: Add foreign key constraint
        Schema::table('purchase_class_budgets', function (Blueprint $table) {
            $table->foreign('purchase_class_id')
                ->references('id')->on('purchase_classes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse changes if needed
        Schema::table('purchase_class_budgets', function (Blueprint $table) {
            $table->string('name');
            $table->dropForeign(['purchase_class_id']);
            $table->dropColumn('purchase_class_id');
        });

        DB::table('purchase_classes')->truncate();
    }
}