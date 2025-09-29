<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_manager', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('name');
            $table->enum('document_type', ['LICENSE', 'CONTRACT', 'CERTIFICATE', 'POLICY', 'AGREEMENT']);

            $table->text('description');

            $table->unsignedInteger('responsible');
            $table->foreign('responsible')->references('id')->on('users');

            $table->unsignedInteger('co_responsible');
            $table->foreign('co_responsible')->references('id')->on('users');

            $table->string('issuing_body');

            $table->date('issue_date');
            $table->decimal('cost_of_renewal', 10, 2);
            $table->date('renewal_date');
            $table->date('expiry_date');
            $table->unsignedInteger('alert_days_before')->default(21);

            $table->enum('status', ['ACTIVE', 'EXPIRED', 'ARCHIVED'])->default('ACTIVE');

            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');

            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users');

            $table->unsignedInteger('ins');
            $table->foreign('ins')->references('id')->on('companies');

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
        Schema::dropIfExists('document_manager');
    }
}
