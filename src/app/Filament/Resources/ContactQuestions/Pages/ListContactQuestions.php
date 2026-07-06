<?php

namespace App\Filament\Resources\ContactQuestions\Pages;

use App\Filament\Resources\ContactQuestions\ContactQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactQuestions extends ListRecords
{
    protected static string $resource = ContactQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
