<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('room_id'); // Foreign Key to Rooms table
            $table->unsignedBigInteger('project_id')->nullable(); // Foreign Key to Projects table
            $table->string('title', 255); // Booking Title
            $table->datetime('start_time'); // Start Time
            $table->datetime('end_time'); // End Time
            $table->enum('status', ['booked', 'confirmed', 'cancelled']);
            $table->enum('booking_type', ['internal', 'eksternal']);
            $table->unsignedBigInteger('booked_by');
            $table->json('internal_participants')->nullable();
            $table->json('external_participants')->nullable();
            $table->integer('number_of_participants')->nullable();
            $table->datetime('confirmed_at')->nullable(); // Confirmation Timestamp
            $table->timestamps(); // Created and Updated timestamps
            $table->text('note')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->json('additional_requests')->nullable();

            // Foreign Key Constraints
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('booked_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
