<?php

namespace App\Filament\Resources\Recipes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RecipeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('logo')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('content')
                    ->columnSpanFull(),
                TextEntry::make('portions')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('calories')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('views')
                    ->numeric(),
                TextEntry::make('author_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('total_time_minutes')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('time_raw')
                    ->placeholder('-'),
                TextEntry::make('rating')
                    ->numeric(),
            ]);
    }
}
