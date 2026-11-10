<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RecipeProduct extends Pivot
{
    protected $table = 'recipe_product';

    public $timestamps = false;

    protected $casts = [
        'amount_base' => 'decimal:3',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function getAmountUnitAttribute(): string
    {
        return $this->amountUnitForLocale();
    }

    public function amountUnitForLocale(?string $locale = null): string
    {
        $amount = $this->attributes['amount'] ?? null;
        $unitName = $this->unit?->localizedName($locale) ?? '';

        return trim(floatval($amount).' '.$unitName);
    }

    public function getCalculatedAmountAttribute(): ?float
    {
        $factor = $this->unit?->to_base_factor;

        if (! $factor) {
            return null;
        }

        $amountBase = $this->attributes['amount_base'] ?? 0;

        return number_format(round((float) $amountBase / (float) $factor, 3), 3, '.', '');
    }
}
