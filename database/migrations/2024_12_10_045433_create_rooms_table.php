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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('room_name', 200); // Room Name
            $table->integer('capacity'); // Capacity
            $table->integer('floor'); // Floor Number
            $table->boolean('bookable')->default(true); // Bookable Status
            $table->boolean('mergeable')->default(false); // Mergeable Status
            $table->json('facilities')->nullable(); // JSON column for facilities
            $table->json('room_pictures')->nullable();
            $table->json('room_layouts')->nullable();
            $table->datetime('not_available_from')->nullable(); // Datetime to set room available for booking
            $table->datetime('not_available_to')->nullable(); // Datetime to set room available for booking
            $table->text('remark')->nullable();
            $table->enum('designation', ['internal', 'eksternal'])->default('internal');
            $table->timestamps(); // Created and Updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
