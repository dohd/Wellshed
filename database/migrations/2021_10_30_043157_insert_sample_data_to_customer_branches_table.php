<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertSampleDataToCustomerBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_branches', function (Blueprint $table) {
            //
        });

        $data = [
            ['customer_id' => 1, 'branch_id' => 2],
            ['customer_id' => 2, 'branch_id' => 3],
            ['customer_id' => 1, 'branch_id' => 3],
            ['customer_id' => 2, 'branch_id' => 2],
            ['customer_id' => 1, 'branch_id' => 6],
            ['customer_id' => 2, 'branch_id' => 8],
            ['customer_id' => 1, 'branch_id' => 9],
            ['customer_id' => 2, 'branch_id' => 10],
            ['customer_id' => 1, 'branch_id' => 11],
            ['customer_id' => 2, 'branch_id' => 12],
        ];
        DB::table('customer_branches')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_branches', function (Blueprint $table) {
            //
        });

        DB::table('banks')->delete();
    }
}
