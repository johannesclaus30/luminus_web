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
        if (! Schema::hasColumn('perks', 'deleted_at')) {
            Schema::table('perks', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('perks', 'deleted_at')) {
            Schema::table('perks', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
