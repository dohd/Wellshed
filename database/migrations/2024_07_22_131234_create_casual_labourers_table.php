<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasualLabourersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('casual_labourers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tid');
            $table->string('name');
            $table->string('id_number')->nullable();

            $table->unsignedBigInteger('job_category_id')->nullable();
            $table->foreign('job_category_id')->references('id')->on('job_categories');

            $table->enum('work_type',['contract','non_contract'])->defaultValue('non_contract');
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->enum('status',['active','suspended','terminated'])->defaultValue('active');
            $table->enum('gender',['male','female','unspecified'])->defaultValue('unspecified');
            $table->decimal('rate', 10,2)->default(0);
            $table->string('kin_name')->nullable();
            $table->string('kin_contact')->nullable();
            $table->enum('kin_relationship',['Wife','Husband','Father','Mother','Brother','Sister', 'Son','Daughter'])->defaultValue('');
            $table->unsignedBigInteger('ins');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('casual_labourers');
    }
}
