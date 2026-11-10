<?php

namespace App\Filament\Resources\Translations;

use App\Filament\Resources\Translations\Pages\ManageTranslations;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Models\Translation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TranslationResource extends Resource
{
    protected static ?string $model = Translation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('translatable_type')
                    ->options([
                        Recipe::class => 'Recipe',
                        RecipeCategory::class => 'Recipe category',
                    ])
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('translatable_id', null))
                    ->required(),
                Select::make('translatable_id')
                    ->label('Entity record')
                    ->options(fn (Get $get): array => match ($get('translatable_type')) {
                        Recipe::class => Recipe::query()
                            ->orderBy('id')
                            ->pluck('title', 'id')
                            ->all(),
                        RecipeCategory::class => RecipeCategory::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all(),
                        default => [],
                    })
                    ->searchable()
                    ->required(),
                Select::make('locale')
                    ->options(config('app.available_locales'))
                    ->required()
                    ->native(false),
                TextInput::make('key')
                    ->required()
                    ->maxLength(255),
                Textarea::make('value')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('key')
            ->columns([
                TextColumn::make('translatable_type')
                    ->label('Entity')
                    ->toggleable(),
                TextColumn::make('translatable_id')
                    ->label('Entity ID')
                    ->sortable(),
                TextColumn::make('locale')
                    ->searchable(),
                TextColumn::make('key')
                    ->searchable(),
                TextColumn::make('value')
                    ->searchable()
                    ->limit(60),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTranslations::route('/'),
        ];
    }
}
