<?php

namespace App\Filament\Resources\RecipeCategories\Pages;

use App\Filament\Resources\RecipeCategories\RecipeCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRecipeCategories extends ManageRecords
{
    protected static string $resource = RecipeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
