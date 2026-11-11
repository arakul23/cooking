<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecipeRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_individual_ratings_and_updates_the_average(): void
    {
        $recipe = Recipe::create(['rating_avg' => 0]);
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->actingAs($firstUser, 'api');
        $this->postJson("/api/recipes/{$recipe->id}/rating", ['rating' => 4.5])
            ->assertOk()
            ->assertJsonPath('rating_avg', 4.5);

        $this->actingAs($secondUser, 'api');
        $this->postJson("/api/recipes/{$recipe->id}/rating", ['rating' => 2.5])
            ->assertOk()
            ->assertJsonPath('rating_avg', 3.5)
            ->assertJsonPath('user_rating', 2.5)
            ->assertJsonStructure(['rating_available_at']);

        $this->assertDatabaseHas('recipe_ratings', [
            'recipe_id' => $recipe->id,
            'user_id' => $firstUser->id,
            'rating' => 4.5,
        ]);
        $this->assertDatabaseHas('recipe_ratings', [
            'recipe_id' => $recipe->id,
            'user_id' => $secondUser->id,
            'rating' => 2.5,
        ]);
        $this->assertSame(3.5, (float) $recipe->fresh()->rating_avg);

        $recipeResponse = $this->getJson("/api/recipes/{$recipe->id}")
            ->assertOk()
            ->assertJsonPath('data.user_rating', 2.5)
            ->assertJsonStructure(['data' => ['rating_available_at']]);
        $this->assertNotNull($recipeResponse->json('data.rating_available_at'));
    }

    public function test_it_limits_rating_changes_to_once_per_minute_per_recipe(): void
    {
        $recipe = Recipe::create(['rating_avg' => 0]);
        $this->actingAs(User::factory()->create(), 'api');

        $this->postJson("/api/recipes/{$recipe->id}/rating", ['rating' => 4])
            ->assertOk();
        $this->postJson("/api/recipes/{$recipe->id}/rating", ['rating' => 3])
            ->assertTooManyRequests();
    }

    public function test_it_requires_authentication_and_a_valid_rating(): void
    {
        $recipe = Recipe::create(['rating_avg' => 0]);

        $this->postJson("/api/recipes/{$recipe->id}/rating", ['rating' => 4])
            ->assertUnauthorized();

        $this->actingAs(User::factory()->create(), 'api');
        $this->postJson("/api/recipes/{$recipe->id}/rating", ['rating' => 5.01])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_loading_a_recipe_recalculates_a_stale_average_from_database_votes(): void
    {
        $recipe = Recipe::create(['rating_avg' => 4.39]);
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $timestamp = now();

        DB::table('recipe_ratings')->insert([
            [
                'recipe_id' => $recipe->id,
                'user_id' => $firstUser->id,
                'rating' => 4.39,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'recipe_id' => $recipe->id,
                'user_id' => $secondUser->id,
                'rating' => 2.39,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);

        $this->actingAs($secondUser, 'api')
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertOk()
            ->assertJsonPath('data.rating_avg', 3.39);

        $this->assertSame(3.39, (float) $recipe->fresh()->rating_avg);
    }
}
