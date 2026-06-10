<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
            ->withPivot('amount', 'unit_id', 'amount_base', 'note');
    }
}
