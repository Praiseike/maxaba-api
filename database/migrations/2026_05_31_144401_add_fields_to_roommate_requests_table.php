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
        Schema::table('roommate_requests', function (Blueprint $table) {
            $table->string('title')->nullable()->after('user_id');
            $table->string('house_image')->nullable()->after('interests');
            $table->json('map')->nullable()->after('house_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roommate_requests', function (Blueprint $table) {
            $table->dropColumn(['title', 'house_image', 'map']);
        });
    }
};
