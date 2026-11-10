<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecipeFridgeSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_matching_recipes_and_lists_missing_ingredients(): void
    {
        $breakfast = RecipeCategory::create(['name' => 'Breakfast']);
        $dinner = RecipeCategory::create(['name' => 'Dinner']);
        $flour = Product::create(['name' => 'Flour']);
        $eggs = Product::create(['name' => 'Eggs']);
        $milk = Product::create(['name' => 'Milk']);
        $kilogram = $this->createUnit('kg', 'mass', 1000);
        $gram = $this->createUnit('g', 'mass', 1);
        $piece = $this->createUnit('piece', 'count', 1);

        $matchingRecipe = $this->createRecipe($breakfast, 'Pancakes', [
            [$flour->id, 500, $gram],
            [$eggs->id, 1, $piece],
        ]);
        $this->createRecipe($breakfast, 'Too much flour', [
            [$flour->id, 1500, $gram],
        ]);
        $this->createRecipe($dinner, 'Wrong category', [
            [$flour->id, 500, $gram],
        ]);
        $this->createRecipe($breakfast, 'Missing milk', [
            [$flour->id, 100, $gram],
            [$milk->id, 100, $gram],
        ]);

        $this->postJson('/api/recipes/fridge-search', [
            'category_id' => $breakfast->id,
            'locale' => 'en',
            'ingredients' => [
                ['product_id' => $flour->id, 'amount' => 1, 'unit_id' => $kilogram],
                ['product_id' => $eggs->id, 'amount' => 1, 'unit_id' => $piece],
            ],
        ])
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $matchingRecipe->id)
            ->assertJsonPath('data.0.title', 'Pancakes')
            ->assertJsonPath('data.0.missing_products', [])
            ->assertJsonCount(2, 'data.0.products')
            ->assertJsonPath('data.0.products.0.name', 'Flour')
            ->assertJsonFragment([
                'name' => 'Milk',
                'amount_unit' => '100 g',
            ])
            ->assertJsonFragment([
                'name' => 'Flour',
                'amount_unit' => '500 g',
            ]);
    }

    public function test_it_returns_a_recipe_with_only_some_of_its_ingredients_available(): void
    {
        $category = RecipeCategory::create(['name' => 'Soup']);
        $pork = Product::create(['name' => 'Pork 400 g']);
        $rice = Product::create(['name' => 'Rice']);
        $gram = $this->createUnit('g', 'mass', 1);
        $recipe = $this->createRecipe($category, 'Kharcho soup', [
            [$pork->id, 400, $gram],
            [$rice->id, 100, $gram],
        ]);

        $this->postJson('/api/recipes/fridge-search', [
            'category_id' => $category->id,
            'locale' => 'en',
            'ingredients' => [
                ['product_id' => $pork->id, 'amount' => 400, 'unit_id' => $gram],
            ],
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $recipe->id)
            ->assertJsonPath('data.0.missing_products.0.name', 'Rice')
            ->assertJsonPath('data.0.missing_products.0.amount_unit', '100 g');
    }

    public function test_it_excludes_recipes_with_more_than_two_missing_products(): void
    {
        $category = RecipeCategory::create(['name' => 'Dinner']);
        $flour = Product::create(['name' => 'Flour']);
        $milk = Product::create(['name' => 'Milk']);
        $eggs = Product::create(['name' => 'Eggs']);
        $butter = Product::create(['name' => 'Butter']);
        $grams = $this->createUnit('g', 'mass', 1);
        $pieces = $this->createUnit('piece', 'count', 1);

        $twoMissing = $this->createRecipe($category, 'Two missing', [
            [$flour->id, 100, $grams],
            [$milk->id, 100, $grams],
            [$eggs->id, 1, $pieces],
        ]);
        $this->createRecipe($category, 'Three missing', [
            [$flour->id, 100, $grams],
            [$milk->id, 100, $grams],
            [$eggs->id, 1, $pieces],
            [$butter->id, 50, $grams],
        ]);

        $this->postJson('/api/recipes/fridge-search', [
            'category_id' => $category->id,
            'ingredients' => [
                ['product_id' => $flour->id, 'amount' => 100, 'unit_id' => $grams],
            ],
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $twoMissing->id)
            ->assertJsonCount(2, 'data.0.missing_products');
    }

    public function test_it_rejects_duplicate_products_and_invalid_units(): void
    {
        $category = RecipeCategory::create(['name' => 'Breakfast']);
        $product = Product::create(['name' => 'Flour']);
        $unit = $this->createUnit('g', 'mass', 1);

        $this->postJson('/api/recipes/fridge-search', [
            'category_id' => $category->id,
            'ingredients' => [
                ['product_id' => $product->id, 'amount' => 1, 'unit_id' => $unit],
                ['product_id' => $product->id, 'amount' => 2, 'unit_id' => $unit],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ingredients.0.product_id', 'ingredients.1.product_id']);
    }

    public function test_it_marks_ingredients_with_incompatible_unit_types_as_missing(): void
    {
        $category = RecipeCategory::create(['name' => 'Breakfast']);
        $flour = Product::create(['name' => 'Flour']);
        $grams = $this->createUnit('g', 'mass', 1);
        $pieces = $this->createUnit('piece', 'count', 1);
        $this->createRecipe($category, 'Bread', [
            [$flour->id, 100, $grams],
        ]);

        $this->postJson('/api/recipes/fridge-search', [
            'category_id' => $category->id,
            'ingredients' => [
                ['product_id' => $flour->id, 'amount' => 1000, 'unit_id' => $pieces],
            ],
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.missing_products.0.name', 'Flour');
    }

    public function test_it_returns_catalog_options_for_the_search_form(): void
    {
        $category = RecipeCategory::create(['name' => 'Breakfast']);
        $grams = $this->createUnit('g', 'mass', 1);
        Recipe::create([])->categories()->attach($category);

        $this->getJson('/api/recipes/fridge-search/options')
            ->assertOk()
            ->assertJsonPath('units.0.id', $grams)
            ->assertJsonPath('categories.0.id', $category->id);
    }

    public function test_it_validates_fridge_search_option_and_product_filters(): void
    {
        $this->getJson('/api/recipes/fridge-search/options?locale=fr')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('locale');

        $this->getJson('/api/recipes/fridge-search/products?search=a')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('search');
    }

    public function test_it_searches_products_by_name_prefix_without_recipe_relations(): void
    {
        $product = Product::create(['name' => 'Flour']);
        Product::create(['name' => 'Flour']);
        Product::create(['name' => 'Milk']);

        $this->getJson('/api/recipes/fridge-search/products?search=Flo')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.name', 'Flour');
    }

    public function test_it_returns_initial_products_when_no_search_term_is_given(): void
    {
        Product::create(['name' => 'Milk']);
        Product::create(['name' => 'Flour']);

        $this->getJson('/api/recipes/fridge-search/products')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Flour');
    }

    private function createUnit(string $code, string $type, float $factor): int
    {
        return DB::table('units')->insertGetId([
            'code' => $code,
            'name' => $code,
            'type' => $type,
            'to_base_factor' => $factor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<int, array{int, float, int}>  $products
     */
    private function createRecipe(RecipeCategory $category, string $title, array $products): Recipe
    {
        $recipe = Recipe::create([]);
        $recipe->categories()->attach($category);
        $recipe->translations()->createMany([
            ['locale' => 'en', 'key' => 'title', 'value' => $title],
            ['locale' => 'en', 'key' => 'description', 'value' => 'A test recipe'],
            ['locale' => 'en', 'key' => 'content', 'value' => 'Mix and cook.'],
        ]);

        foreach ($products as [$productId, $amountBase, $unitId]) {
            $recipe->products()->attach($productId, [
                'amount' => $amountBase,
                'unit_id' => $unitId,
                'amount_base' => $amountBase,
            ]);
        }

        return $recipe;
    }
}
