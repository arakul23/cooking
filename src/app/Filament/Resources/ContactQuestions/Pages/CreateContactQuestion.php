<?php

namespace App\Filament\Resources\ContactQuestions\Pages;

use App\Filament\Resources\ContactQuestions\ContactQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContactQuestion extends CreateRecord
{
    protected static string $resource = ContactQuestionResource::class;
}
