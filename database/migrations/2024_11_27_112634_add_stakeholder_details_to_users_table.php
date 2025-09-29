<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStakeholderDetailsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->boolean('is_stakeholder')->default(false);

            $table->unsignedInteger('sh_authorizer_id')->nullable();
            $table->foreign('sh_authorizer_id')->references('id')->on('users')->onDelete('set null');

            $table->string('sh_primary_contact')->nullable();
            $table->string('sh_secondary_contact')->nullable();
            $table->string('sh_gender')->nullable();
            $table->string('sh_id_number')->nullable();
            $table->string('sh_company', 200)->nullable();
            $table->string('sh_designation', 300)->nullable();

            $table->text('sh_access_reason')->nullable();

            $table->timestamp('sh_access_start')->nullable();
            $table->timestamp('sh_access_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
