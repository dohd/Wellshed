<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertSampleDataToBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banks', function (Blueprint $table) {
            //
        });
        DB::table('banks')->insert([
            [
                'name' => 'KCB Moi Avenue Nairobi',
                'bank' => 'KCB',
                'number' => '254122366',
                'code' => '100',
                'branch' => 'Moi Avenue Nairobi',
                'ins' => 1
            ],
            [
                'name' => 'Co-operative Bank Embakasi Junction Branch',
                'bank' => 'Co-operative Bank',
                'number' => '254256888',
                'code' => '200',
                'branch' => 'Embakasi Junction Branch',
                'ins' => 1
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banks', function (Blueprint $table) {
            //
        });
        DB::table('banks')->delete();
    }
}
