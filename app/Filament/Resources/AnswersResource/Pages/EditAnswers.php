<?php

namespace App\Filament\Resources\AnswersResource\Pages;

use App\Filament\Resources\AnswersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnswers extends EditRecord
{
    protected static string $resource = AnswersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
