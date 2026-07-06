<?php

namespace App\Filament\Resources\ContactQuestions;

use App\Filament\Resources\ContactQuestions\Pages\CreateContactQuestion;
use App\Filament\Resources\ContactQuestions\Pages\EditContactQuestion;
use App\Filament\Resources\ContactQuestions\Pages\ListContactQuestions;
use App\Filament\Resources\ContactQuestions\Schemas\ContactQuestionForm;
use App\Filament\Resources\ContactQuestions\Tables\ContactQuestionsTable;
use App\Models\ContactQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContactQuestionResource extends Resource
{
    protected static ?string $model = ContactQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'ContactQuestion';

    public static function form(Schema $schema): Schema
    {
        return ContactQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactQuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactQuestions::route('/'),
            'create' => CreateContactQuestion::route('/create'),
            'edit' => EditContactQuestion::route('/{record}/edit'),
        ];
    }
}
