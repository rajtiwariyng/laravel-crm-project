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
        Schema::create("merge_history", function (Blueprint $table) {
            $table->id();
            $table->foreignId("master_contact_id")->constrained("contacts")->onDelete("cascade");
            $table->foreignId("merged_contact_id")->constrained("contacts")->onDelete("cascade");
            $table->json("merged_data_snapshot")->nullable(); // Snapshot of merged contact data
            $table->json("merge_details")->nullable(); // Details about the merge (e.g., conflicts resolved)
            $table->foreignId("merged_by_user_id")->nullable()->constrained("users")->onDelete("set null"); // Optional: Link to user who performed merge
            $table->timestamps();

            $table->index("master_contact_id");
            $table->index("merged_contact_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("merge_history");
    }
};