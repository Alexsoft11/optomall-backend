<?php

namespace App\Filament\Resources\AnswersResource\Pages;

use App\Filament\Resources\AnswersResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAnswers extends CreateRecord
{
    protected static string $resource = AnswersResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
