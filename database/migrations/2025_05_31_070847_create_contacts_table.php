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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable(); // Consider constraints based on requirements
            $table->string('phone')->nullable();
            $table->string('gender')->nullable(); // Using string, could use enum if DB supports
            $table->string('profile_image_path')->nullable();
            $table->string('additional_file_path')->nullable();
            $table->unsignedBigInteger('merged_into_contact_id')->nullable();
            $table->string('status')->default('active'); // e.g., active, inactive, merged
            $table->timestamps();

            // Foreign key constraint (optional, ensures integrity if a contact is deleted)
            // $table->foreign('merged_into_contact_id')->references('id')->on('contacts')->onDelete('set null');

            // Indexing
            $table->index('email');
            $table->index('name');
            $table->index('status');
            $table->index('merged_into_contact_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};