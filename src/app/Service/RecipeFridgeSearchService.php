<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Recipe;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RecipeFridgeSearchService
{
    private const int MAX_MISSING_PRODUCTS = 2;

    /**
     * @param array{
     *     category_id: int,
     *     locale?: string,
     *     ingredients: array<int, array{product_id: int, amount: float, unit_id: int}>
     * } $criteria
     * Finds recipes with at least one matching product and reports any ingredients still needed.
     * @return Collection<int, Recipe>
     */
    public function search(array $criteria): Collection
    {
        // Convert pantry quantities to base units so, for example, grams can be compared with kilograms.
        $units = Unit::query()
            ->whereIn('id', collect($criteria['ingredients'])->pluck('unit_id')->unique())
            ->get(['id', 'type', 'to_base_factor'])
            ->keyBy('id');

        $inventory = collect($criteria['ingredients'])
            ->mapWithKeys(function (array $ingredient) use ($units): array {
                $unit = $units->get($ingredient['unit_id']);

                return [
                    $ingredient['product_id'] => [
                        'amount_base' => round((float) $ingredient['amount'] * (float) $unit->to_base_factor, 3),
                        'type' => $unit->type,
                    ],
                ];
            });

        $locale = $criteria['locale'] ?? app()->getLocale();
        $locales = array_unique([$locale, config('app.fallback_locale')]);

        $recipes = Recipe::query()
            ->whereHas('categories', fn (Builder $query) => $query->where('recipe_categories.id', $criteria['category_id']))
            // Keep partial matches; missing ingredients are calculated below and returned with each recipe.
            ->whereHas('products', fn (Builder $query) => $query->whereIn('products.id', $inventory->keys()))
            ->with([
                'categories',
                'products',
                'translations' => fn ($query) => $query
                    ->whereIn('locale', $locales)
                    ->reorder()
                    ->orderByRaw('CASE WHEN locale = ? THEN 0 ELSE 1 END', [$locale]),
            ])
            ->orderByDesc('rating_avg')
            ->orderByDesc('views')
            ->limit(100)
            ->get();

        $requiredUnits = Unit::query()
            ->whereIn('id', $recipes->flatMap(
                fn (Recipe $recipe) => $recipe->products->pluck('pivot.unit_id')
            )->unique())
            ->get(['id', 'type', 'to_base_factor', 'code', 'name'])
            ->keyBy('id');

        foreach ($recipes as $recipe) {
            $missingProducts = [];

            foreach ($recipe->products as $product) {
                $pivot = $product->pivot;
                $unit = $requiredUnits->get($pivot->unit_id);
                $pivot->setRelation('unit', $unit);
                $stock = $inventory->get($product->id);
                $isCompatibleStock = $stock !== null
                    && $unit !== null
                    && $unit->type === $stock['type'];
                // Incompatible units cannot satisfy an ingredient, so none of that pantry quantity is deducted.
                $missingAmountBase = max(
                    0,
                    (float) $pivot->amount_base
                    - ($isCompatibleStock ? $stock['amount_base'] : 0)
                );

                if ($missingAmountBase > 0) {
                    $missingAmount = $unit !== null && (float) $unit->to_base_factor > 0
                        ? round($missingAmountBase / (float) $unit->to_base_factor, 3)
                        : (float) $pivot->amount;
                    $formattedAmount = rtrim(rtrim(number_format($missingAmount, 3, '.', ''), '0'), '.');

                    $missingProducts[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'amount_unit' => trim($formattedAmount.' '.($unit?->localizedName($locale) ?? '')),
                    ];
                }
            }

            $recipe->setAttribute('missing_products', $missingProducts);
        }

        // Hide recipes that require more than two products not available in the user's inventory.
        return $recipes
            ->filter(fn (Recipe $recipe): bool => count($recipe->getAttribute('missing_products')) <= self::MAX_MISSING_PRODUCTS)
            ->sort(function (Recipe $left, Recipe $right): int {
                // Prefer recipes needing fewer ingredients; rating and views break ties.
                $missingCount = count($left->getAttribute('missing_products'))
                    <=> count($right->getAttribute('missing_products'));

                if ($missingCount !== 0) {
                    return $missingCount;
                }

                $rating = (float) $right->rating_avg <=> (float) $left->rating_avg;

                return $rating !== 0 ? $rating : ($right->views <=> $left->views);
            })
            ->values();
    }
}
