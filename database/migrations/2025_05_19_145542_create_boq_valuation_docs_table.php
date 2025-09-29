<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoqValuationDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boq_valuation_docs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('boq_valuation_id')->nullable();
            $table->string('caption', 191)->nullable();
            $table->string('document_name', 191)->nullable();
            $table->integer('ins')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boq_valuation_docs');
    }
}
