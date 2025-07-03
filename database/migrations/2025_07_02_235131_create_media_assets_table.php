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
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->nullable();;
            $table->string('file_path')->comment('Path to the media file');
            $table->string('file_type');
            $table->string('file_name')->nullable()->comment('Name of the media file');
            $table->string('file_size')->nullable()->comment('Size of the media file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
