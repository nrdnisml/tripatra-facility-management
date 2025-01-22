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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('email', 255)->unique(); // Email Address
            $table->string('name')->nullable(); // User Name
            $table->string('password')->nullable(); // User Name
            $table->string('ad_id')->nullable(); // Active Directory ID
            $table->timestamps(); // Created and Updated timestamps
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('user_id'); // Foreign Key to Users table
            $table->string('role_name', 100); // Role Name
            $table->string('module', 100)->nullable(); // Module Name
            $table->timestamps(); // Created and Updated timestamps

            // Foreign Key Constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('sessions');
    }
};