<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerComplainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_complains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('project_id');
            $table->string('employees');
            $table->unsignedBigInteger('solver_id')->nullable();
            $table->text('issue_description');
            $table->decimal('initial_scale', 10,2)->default(0);
            $table->decimal('current_scale', 10,2)->default(0);
            $table->string('type_of_complaint')->nullable();
            $table->enum('status',['pending','complete','in_progress'])->default('pending');
            $table->date('date');
            $table->text('planing')->nullable();
            $table->text('doing')->nullable();
            $table->text('checking')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->text('comments')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ins');
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
        Schema::dropIfExists('customer_complains');
    }
}
