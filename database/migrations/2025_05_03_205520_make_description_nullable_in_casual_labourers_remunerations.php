<?php

use Illuminate\Database\Migrations\Migration;

class MakeDescriptionNullableInCasualLabourersRemunerations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE rose_casual_labourers_remunerations MODIFY description VARCHAR(199) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE rose_casual_labourers_remunerations MODIFY description VARCHAR(199) NOT NULL");
    }
}
