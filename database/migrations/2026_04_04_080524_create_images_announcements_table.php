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
        Schema::create('images_announcements', function (Blueprint $table) {
            $table->id('ImgAnnouncement_ID');

            $table->foreignId('Announcement_ID')
                ->constrained('announcements', 'Announcement_ID')
                ->onDelete('cascade');

            $table->string('ImagePath', 255);
            $table->timestamp('UploadTime')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images_announcements');
    }
};
