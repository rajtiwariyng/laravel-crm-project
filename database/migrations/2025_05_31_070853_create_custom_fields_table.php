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
        Schema::create("custom_fields", function (Blueprint $table) {
            $table->id();
            $table->string("name")->unique(); // Machine-readable name
            $table->string("label"); // Human-readable label
            $table->string("type"); // e.g., text, date, number, textarea
            $table->boolean("is_filterable")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("custom_fields");
    }
};