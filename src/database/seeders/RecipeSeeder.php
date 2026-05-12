<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\RecipeCategory;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = RecipeCategory::query()->pluck('id');

        Recipe::factory()->count(10)->create()->each(function (Recipe $recipe) use ($categoryIds): void {
            $count = min(3, $categoryIds->count());

            if ($count === 0) {
                return;
            }

            $recipe->categories()->attach(
                $categoryIds->random(random_int(1, $count))->all()
            );
        });
    }
}
