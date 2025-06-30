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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('offer_type')->default('rent')->after('occupant_type');
            $table->string('slug')->nullable();
            $table->string('offer_duration')->nullable()->after('offer_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            //
            $table->dropColumn([
                'offer_type',
                'slug',
                'offer_duration'
            ]);
        });
    }
};
