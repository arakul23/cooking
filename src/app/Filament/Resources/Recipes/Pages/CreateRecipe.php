<?php

declare(strict_types=1);

namespace App\Filament\Resources\Recipes\Pages;

use App\Filament\Resources\Recipes\RecipeResource;
use App\Jobs\TranslateRecipeData;
use Filament\Resources\Pages\CreateRecord;

class CreateRecipe extends CreateRecord
{
    protected static string $resource = RecipeResource::class;

    protected function afterCreate(): void
    {
        $model = $this->record;
        $locale = data_get($this, 'data.locale');

        $model->translations()->createMany([
            [
                'locale' => $locale,
                'key' => 'title',
                'value' => data_get($this, 'data.title'),
            ],
            [
                'locale' => $locale,
                'key' => 'description',
                'value' => data_get($this, 'data.description'),
            ],
            [
                'locale' => $locale,
                'key' => 'content',
                'value' => data_get($this, 'data.content'),
            ]
        ],
        );

        TranslateRecipeData::dispatch($model->id);
    }
}
