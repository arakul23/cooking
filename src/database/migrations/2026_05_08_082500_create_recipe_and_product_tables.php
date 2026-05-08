<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('logo')->nullable();
            $table->text('description');
            $table->text('content');
            $table->unsignedInteger('portions')->nullable();
            $table->unsignedInteger('calories')->nullable();
            $table->timestamps();
        });

        Schema::create('recipe_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'product_category_id']);
        });

        Schema::create('category_recipe', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('recipe_categories')->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'recipe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_recipe');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('recipe_categories');
        Schema::dropIfExists('recipes');
    }
};
