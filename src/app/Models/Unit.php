<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    public function localizedName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $name = (string) ($this->attributes['name'] ?? '');
        $code = strtolower((string) ($this->attributes['code'] ?? ''));

        $translations = array_merge(
            config('units.translations.' . strtolower(trim($name)), []),
            config("units.translations.{$code}", []), // code приоритетнее name
        );

        foreach (array_unique([$locale, config('app.fallback_locale')]) as $loc) {
            if (isset($translations[$loc])) {
                return $translations[$loc];
            }
        }

        return $name;
    }

    public function getNameAttribute(?string $value): ?string
    {
        return $value === null ? null : $this->localizedName();
    }
}
