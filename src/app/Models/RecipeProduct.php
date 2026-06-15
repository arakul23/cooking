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
        // Вместо $this->amount берем напрямую из атрибутов
        $amount = $this->attributes['amount'] ?? null;
        $unitName = $this->unit?->name ?? '';

        return trim($amount . ' ' . $unitName);
    }

    public function getCalculatedAmountAttribute(): ?float
    {
        $factor = $this->unit?->to_base_factor;

        if (! $factor) {
            return null;
        }

        $amountBase = $this->attributes['amount_base'] ?? 0;

        return round((float) $amountBase / (float) $factor, 3);
    }
}
