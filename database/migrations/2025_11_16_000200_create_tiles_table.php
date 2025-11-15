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
        Schema::create('tiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exposition_id')->constrained()->cascadeOnDelete();
            $table->string('tile_identifier');
            $table->unsignedSmallInteger('type');
            $table->json('position');
            $table->json('rotation');
            $table->timestamps();

            $table->unique(['exposition_id', 'tile_identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiles');
    }
};
