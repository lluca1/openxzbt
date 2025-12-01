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
        Schema::create('exhibits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exposition_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();


            $table->string('media_type')->nullable();      
            $table->string('media_path');               
            $table->string('thumbnail_path')->nullable();  
            $table->string('mime_type')->nullable();       

            $table->unsignedInteger('position')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exhibits');
    }
};
