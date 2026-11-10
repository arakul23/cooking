<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public static function sanitizeName(string $name): string
    {
        $sanitized = preg_replace('/[^\p{L}\p{M}\p{N}\p{Z}\s"]/u', '', $name);

        if ($sanitized === null) {
            throw new \InvalidArgumentException('Product names must contain valid UTF-8 text.');
        }

        $sanitized = preg_replace('/[\p{Z}\s]+/u', ' ', $sanitized);

        return trim($sanitized ?? '');
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = self::sanitizeName($value);
    }

    public function getNameAttribute(?string $value): ?string
    {
        return $value === null ? null : self::sanitizeName($value);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'category_product');
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_product');
    }
}
