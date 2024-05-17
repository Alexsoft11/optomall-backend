<?php

namespace App\Filament\Resources\ExchangesResource\Pages;

use App\Filament\Resources\ExchangesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExchanges extends EditRecord
{
    protected static string $resource = ExchangesResource::class;

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
