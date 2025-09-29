<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotesToAdvancePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advance_payments', function (Blueprint $table) {

            $table->text('notes')->nullable()->after('amount');


            DB::table('permissions')->insert([
                'name' => 'advance-payment-super-applicant',
                'display_name' => 'Advance Payment Super Applicant Permission',
                'module_id' => 19,
                'created_at' => now(),
                'updated_at' => now()
            ]);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advance_payments', function (Blueprint $table) {

            DB::table('permissions')
                ->where('name', 'advance-payment-super-applicant')
                ->delete();
        });
    }
}
