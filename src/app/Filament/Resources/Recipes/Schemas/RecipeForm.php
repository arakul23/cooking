<?php

namespace App\Filament\Resources\Recipes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('locale')
                    ->options([
                        'en' => 'en',
                        'uk' => 'uk',
                    ]),
                TextInput::make('title')
                    ->required(),
                TextInput::make('logo'),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('portions')
                    ->numeric(),
                TextInput::make('calories')
                    ->numeric(),
                Select::make('author_id')
                    ->label('Author')
                    ->relationship('author', 'name')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('categories', 'name')
                    ->searchable(['name'])
                    ->preload()
                    ->multiple()
                    ->required(),
                TextInput::make('total_time_minutes')
                    ->integer()
                    ->minValue(0),
                TextInput::make('time_raw'),
            ]);
    }
}
