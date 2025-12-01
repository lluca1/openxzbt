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
        Schema::table('expositions', function (Blueprint $table) {
            $table->json('player_spawn')->nullable()->after('preset_theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expositions', function (Blueprint $table) {
            $table->dropColumn('player_spawn');
        });
    }
};
