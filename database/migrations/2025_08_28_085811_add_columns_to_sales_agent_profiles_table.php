<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSalesAgentProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_agent_profiles', function (Blueprint $table) {
            $table->string('employment_status')->nullable();
            $table->json('professional_courses')->nullable();
            $table->text('describe_yourself')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_agent_profiles', function (Blueprint $table) {
            //
        });
    }
}
