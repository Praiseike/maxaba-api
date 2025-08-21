<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_mode', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->text('message')->nullable();
            $table->timestamp('scheduled_start')->nullable();
            $table->timestamp('scheduled_end')->nullable();
            $table->timestamps();
        });

        // Insert default record
        DB::table('maintenance_mode')->insert([
            'is_enabled' => false,
            'message' => 'We are currently performing scheduled maintenance. Please check back soon.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_mode');
    }
};