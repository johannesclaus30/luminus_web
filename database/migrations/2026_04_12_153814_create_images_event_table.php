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
        Schema::create('images_event', function (Blueprint $table) {
            $table->id('ImgEvent_ID'); // Primary Key from ERD
            $table->foreignId('Events_ID')->constrained('events', 'Events_ID')->onDelete('cascade');
            $table->string('ImagePath');
            $table->timestamp('CreatedAt')->useCurrent(); // Matches your CreatedAt: TIMESTAMP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images_event');
    }
};
