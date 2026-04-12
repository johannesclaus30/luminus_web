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
        Schema::create('admins', function (Blueprint $table) {
            $table->id('Admin_ID');
            $table->string('AdminFirstName');
            $table->string('AdminMiddleName')->nullable();
            $table->string('AdminLastName');
            $table->string('AdminEmail')->unique();
            $table->string('AdminPasswordHash');
            $table->enum('AdminRole', ['ExecutiveDirector', 'AcademicDirector', 'Coordinator', 'Staff']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
