<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCategoryEnumOnClientFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE rose_client_feedback MODIFY category ENUM('Quality Concern', 'Complaint', 'Customer Direct Message')");
    }

    public function down()
    {
        // Revert back to the original enum values
        DB::statement("ALTER TABLE rose_client_feedback MODIFY category ENUM('Quality Concern', 'Complaint')");
    }}
