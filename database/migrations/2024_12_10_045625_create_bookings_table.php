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
            $table->enum('status', ['booked', 'confirmed', 'cancelled']); // Booking Status
            $table->enum('booking_type', ['internal', 'eksternal']); // Booking Type
            $table->unsignedBigInteger('booked_by'); // Foreign Key to Users table
            $table->json('booked_for')->nullable(); // JSON column for booked users
            $table->datetime('confirmed_at')->nullable(); // Confirmation Timestamp
            $table->timestamps(); // Created and Updated timestamps
            $table->text('remark')->nullable();
            $table->string('cancelled_by')->nullable();

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