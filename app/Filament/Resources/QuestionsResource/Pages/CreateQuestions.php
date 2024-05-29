<?php

namespace App\Filament\Resources\QuestionsResource\Pages;

use App\Filament\Resources\QuestionsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestions extends CreateRecord
{
    protected static string $resource = QuestionsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    } 
}
