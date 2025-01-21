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
        Schema::create('room_connections', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('room_id'); // Foreign Key to Rooms table
            $table->unsignedBigInteger('connected_room_id'); // Foreign Key to Connected Rooms

            // Foreign Key Constraints
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('connected_room_id')->references('id')->on('rooms')->onDelete('cascade');

            // Ensure Unique Room Connections
            // $table->unique(['room_id', 'connected_room_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_connections');
    }
};
