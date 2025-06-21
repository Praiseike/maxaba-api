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
        Schema::create('user_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // the user being followed
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade'); // the one following
            $table->timestamps();
        
            $table->unique(['user_id', 'follower_id']); // prevent duplicate follows
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_followers');
    }
};
