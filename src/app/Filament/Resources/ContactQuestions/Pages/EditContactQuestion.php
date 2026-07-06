<?php

namespace App\Filament\Resources\ContactQuestions\Pages;

use App\Filament\Resources\ContactQuestions\ContactQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContactQuestion extends EditRecord
{
    protected static string $resource = ContactQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
