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
            $table->string('floor_texture')->nullable()->after('cover_image_path');
            $table->string('ceiling_texture')->nullable()->after('floor_texture');
            $table->string('wall_texture')->nullable()->after('ceiling_texture');
            $table->string('ambient_track')->nullable()->after('wall_texture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expositions', function (Blueprint $table) {
            $table->dropColumn([
                'floor_texture',
                'ceiling_texture',
                'wall_texture',
                'ambient_track',
            ]);
        });
    }
};
