<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeKinRelationshipNullableInCasualLabourers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE rose_casual_labourers MODIFY kin_relationship ENUM('Wife','Husband','Father','Mother','Brother','Sister','Son','Daughter') NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE rose_casual_labourers MODIFY kin_relationship ENUM('Wife','Husband','Father','Mother','Brother','Sister','Son','Daughter') NOT NULL");    
    }
}
