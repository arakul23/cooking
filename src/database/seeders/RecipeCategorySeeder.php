<?php

namespace Database\Seeders;

use App\Models\RecipeCategory;
use Illuminate\Database\Seeder;

class RecipeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Breakfast',
            'Lunch',
            'Dinner',
            'Dessert',
            'Vegan',
            'Gluten Free',
        ];

        foreach ($categories as $category) {
            RecipeCategory::query()->create([
                'name' => $category,
            ]);
        }
    }
}
