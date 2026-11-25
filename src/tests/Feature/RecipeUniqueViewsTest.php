<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeUniqueViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_counts_repeat_guest_views_once_but_keeps_total_views(): void
    {
        $recipe = Recipe::create(['rating_avg' => 0]);

        $this->withHeaders(['X-Visitor-Id' => '2de6ac0c-ec61-4d35-9e47-95566f0fa001'])
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertOk()
            ->assertJsonPath('data.views', 1)
            ->assertJsonPath('data.unique_views', 1);

        $this->withHeaders(['X-Visitor-Id' => '2de6ac0c-ec61-4d35-9e47-95566f0fa001'])
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertOk()
            ->assertJsonPath('data.views', 2)
            ->assertJsonPath('data.unique_views', 1);

        $this->withHeaders(['X-Visitor-Id' => '7d269d29-603b-4ce8-84cf-c36a320fd4fc'])
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertOk()
            ->assertJsonPath('data.views', 3)
            ->assertJsonPath('data.unique_views', 2);

        $this->assertDatabaseCount('recipe_unique_views', 2);
    }

    public function test_it_counts_authenticated_users_by_account(): void
    {
        $recipe = Recipe::create(['rating_avg' => 0]);
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->withHeaders(['User-Agent' => 'browser-one'])
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertOk()
            ->assertJsonPath('data.unique_views', 1);

        $this->withHeaders(['User-Agent' => 'browser-two'])
            ->getJson("/api/recipes/{$recipe->id}")
            ->assertOk()
            ->assertJsonPath('data.unique_views', 1);

        $this->assertDatabaseCount('recipe_unique_views', 1);
    }
}
