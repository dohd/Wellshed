<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_leads', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);
            $table->string('client_name', 255)->nullable();
            $table->string('phone_no', 20)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('project', 255)->nullable();
            $table->string('product_brand', 255)->nullable();
            $table->string('product_spec', 255)->nullable();
            $table->unsignedBigInteger('ins')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_leads');
    }
}
