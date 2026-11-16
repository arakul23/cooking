<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_and_remove_a_favorite_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create(['rating_avg' => 0]);

        $this->actingAs($user, 'api')
            ->postJson("/api/recipes/favourite/{$recipe->id}")
            ->assertNoContent();

        $this->assertDatabaseHas('favourites_recipes', [
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        $this->actingAs($user, 'api')
            ->postJson("/api/recipes/favourite/{$recipe->id}")
            ->assertNoContent();

        $this->assertDatabaseCount('favourites_recipes', 1);

        $this->deleteJson("/api/recipes/favourite/{$recipe->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('favourites_recipes', [
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);
    }

    public function test_adding_a_favorite_requires_authentication(): void
    {
        $recipe = Recipe::create(['rating_avg' => 0]);

        $this->postJson("/api/recipes/favourite/{$recipe->id}")
            ->assertUnauthorized();
    }

    public function test_favorite_list_includes_translated_title_and_description(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create([
            'rating_avg' => 4.5,
            'total_time_minutes' => 30,
        ]);
        $recipe->translations()->createMany([
            ['locale' => 'uk', 'key' => 'title', 'value' => 'Борщ'],
            ['locale' => 'uk', 'key' => 'description', 'value' => 'Короткий опис'],
        ]);
        $user->favouriteRecipes()->attach($recipe);

        $this->actingAs($user, 'api')
            ->getJson('/api/recipes/favourite')
            ->assertOk()
            ->assertJsonPath('0.title', 'Борщ')
            ->assertJsonPath('0.description', 'Короткий опис')
            ->assertJsonPath('0.rating_avg', 4.5)
            ->assertJsonPath('0.total_time_minutes', 30);
    }
}
