<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    public function localizedName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $code = strtolower((string) ($this->attributes['code'] ?? ''));
        $translations = config("units.translations.{$code}");

        return $translations[$locale]
            ?? $translations[config('app.fallback_locale')]
            ?? $this->attributes['name']
            ?? '';
    }

    public function getNameAttribute(?string $value): ?string
    {
        return $value === null ? null : $this->localizedName();
    }
}
