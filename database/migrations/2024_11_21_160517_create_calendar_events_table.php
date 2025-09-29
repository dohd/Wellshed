<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_events', function (Blueprint $table) {

            $table->string('event_number')->primary(); // event_number as primary key (string type)
            $table->string('title'); // event title
            $table->text('description')->nullable(); // event description, nullable
            $table->string('location');
            // event location
            $table->unsignedInteger('organizer'); // event organizer (foreign key to users table)
            $table->foreign('organizer')->references('id')->on('users')->onDelete('cascade'); // foreign key constraint

            $table->timestamp('start'); // event start time
            $table->timestamp('end'); // event end time
            $table->string('color', 7)->default("#718FFA"); // event color (hex code)
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
        Schema::dropIfExists('calendar_events');
    }
}
