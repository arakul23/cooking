<?php

namespace App\Filament\Resources\OAuthProviders;

use App\Filament\Resources\OAuthProviders\Pages\ManageOAuthProviders;
use App\Models\OAuthProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OAuthProviderResource extends Resource
{
    protected static ?string $model = OAuthProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'provider';

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('provider')
            ->columns([
                TextColumn::make('provider')
                    ->searchable(),
                TextColumn::make('provider_id')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOAuthProviders::route('/'),
        ];
    }
}
