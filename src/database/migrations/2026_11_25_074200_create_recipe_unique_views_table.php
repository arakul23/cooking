<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_unique_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_key', 64);
            $table->timestamps();

            $table->unique(['recipe_id', 'visitor_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_unique_views');
    }
};
