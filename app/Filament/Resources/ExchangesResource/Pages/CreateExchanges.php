<?php

namespace App\Filament\Resources\ExchangesResource\Pages;

use App\Filament\Resources\ExchangesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExchanges extends CreateRecord
{
    protected static string $resource = ExchangesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
