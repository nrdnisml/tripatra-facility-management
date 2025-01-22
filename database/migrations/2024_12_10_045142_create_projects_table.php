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
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('project_name', 255); // Project Name
            $table->json('project_admin')->nullable(); // JSON column for admins
            $table->json('project_manager')->nullable(); // JSON column for managers
            $table->boolean('is_active')->default(true); // Active status
            $table->timestamps(); // Created and Updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};