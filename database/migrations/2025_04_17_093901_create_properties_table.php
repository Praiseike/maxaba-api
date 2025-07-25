<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->enum('occupant_type', ['single', 'multiple', 'both']);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable();
            $table->json('location')->nullable();
            $table->unsignedInteger('bedrooms')->default(0);
            $table->unsignedInteger('bathrooms')->default(0);
            $table->unsignedInteger('livingrooms')->default(0);
            $table->json('amenities')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->json('images')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('verified')->default(false);
            $table->string('status')->default('pending');
            $table->string('title');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
