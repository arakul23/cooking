<?php

namespace App\Filament\Resources\Recipes\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('pivot.amount')
                    ->label('Amount')
                    ->numeric(decimalPlaces: 3),
                TextColumn::make('pivot.unit_id')
                    ->label('Unit')
                    ->formatStateUsing(function ($state): string {
                        static $units = null;

                        $units ??= DB::table('units')->pluck('name', 'id')->all();

                        return $units[$state] ?? (string) $state;
                    }),
                TextColumn::make('pivot.amount_base')
                    ->label('Base amount')
                    ->numeric(decimalPlaces: 3),
                TextColumn::make('pivot.note')
                    ->label('Note'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name'])
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('amount')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->required(),
                        Select::make('unit_id')
                            ->label('Unit')
                            ->options(fn (): array => DB::table('units')->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('amount_base')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->required(),
                        Textarea::make('note')
                            ->rows(2)
                            ->maxLength(255),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
