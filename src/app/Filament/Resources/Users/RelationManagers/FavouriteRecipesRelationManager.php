<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Recipes\RecipeResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Recipe;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class FavouriteRecipesRelationManager extends RelationManager
{
    protected static string $relationship = 'favouriteRecipes';

    protected static ?string $relatedResource = RecipeResource::class;

    protected static ?string $recordTitleAttribute = 'title'; // подтянет accessor getTitleAttribute()

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                AttachAction::make(),
            ]);
    }
}
