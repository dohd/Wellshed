<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_grades', function (Blueprint $table) {

            $table->bigIncrements('id');

            // Add decimal columns for each 'a_upper', 'a_lower', 'b_upper', and 'b_lower'
            for ($i = 1; $i <= 10; $i++) {
                $table->decimal("{$i}a_upper", 22, 2)->default(0.00);
                $table->decimal("{$i}a_lower", 22, 2)->default(0.00);
                $table->decimal("{$i}b_upper", 22, 2)->default(0.00);
                $table->decimal("{$i}b_lower", 22, 2)->default(0.00);
            }

            // Add foreign key for 'ins'
            $table->unsignedInteger('ins')->unique();
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
        Schema::dropIfExists('job_grades');
    }
}
