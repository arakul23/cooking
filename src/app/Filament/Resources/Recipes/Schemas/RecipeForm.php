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
                TextInput::make('total_time_minutes')
                    ->integer()
                    ->minValue(0),
                TextInput::make('time_raw'),
            ]);
    }
}
