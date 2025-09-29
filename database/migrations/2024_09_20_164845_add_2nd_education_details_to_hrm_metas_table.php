<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Add2ndEducationDetailsToHrmMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hrm_metas', function (Blueprint $table) {



            $table->integer('1st_award_year')->nullable()->after('highest_education_level');

            $table->string('2nd_education_level')->nullable()->after('1st_award_year');
            $table->string('2nd_institution')->nullable()->after('2nd_education_level');
            $table->string('2nd_course')->nullable()->after('2nd_institution');
            $table->string('2nd_award')->nullable()->after('2nd_course');
            $table->integer('2nd_award_year')->nullable()->after('2nd_award');


            $table->string('job_grade')->nullable()->after('2nd_award_year');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrm_metas', function (Blueprint $table) {
            //
        });
    }
}
