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
        Schema::create('events', function (Blueprint $table) {
            $table->id('Events_ID');
            // This links to your new Admin table specifically
            $table->foreignId('Admin_ID')->constrained('admins', 'Admin_ID'); 
            
            $table->string('Title');
            $table->text('Description');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->string('Location');
            $table->integer('MaxCapacity');
            $table->string('Status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
