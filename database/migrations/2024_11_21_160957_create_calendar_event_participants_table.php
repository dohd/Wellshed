<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarEventParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_event_participants', function (Blueprint $table) {

            $table->bigIncrements('id'); // auto-incremented primary key

            $table->string('event_number'); // foreign key referencing the calendar_events table
            $table->foreign('event_number')->references('event_number')->on('calendar_events')->onDelete('cascade'); // foreign key constraint for event_number

            $table->unsignedInteger('user_id'); // foreign key referencing the users table (participant)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // foreign key constraint for user_id

            $table->unique(['event_number', 'user_id']); // unique constraint to prevent duplicate participants for the same event

            $table->timestamps(); // created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_event_participants');
    }
}
