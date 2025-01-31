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
            $table->json('connected_rooms'); // Foreign Key to Connected Rooms
            $table->integer('capacity')->nullable();
            $table->integer('floor')->nullable();
            $table->json('room_pictures')->nullable();
            $table->json('room_layouts')->nullable();
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
