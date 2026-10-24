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
        Schema::create('design_previews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('data');
            $table->string('mime', 50)->default('image/webp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_previews');
    }
};
