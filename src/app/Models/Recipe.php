<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Recipe extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            RecipeCategory::class,
            'category_recipe',
            'recipe_id',
            'category_id'
        );
    }

    public function products(): BelongsToMany
    {
        return $this
            ->belongsToMany(Product::class, 'recipe_product')
            ->using(RecipeProduct::class)
            ->withPivot('amount', 'unit_id', 'amount_base', 'note');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable')
            ->orderByRaw("CASE WHEN locale = ? THEN 0 ELSE 1 END", [App()->getLocale()]);
    }

    public function favouriteByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favourites_recipes');
    }
}
