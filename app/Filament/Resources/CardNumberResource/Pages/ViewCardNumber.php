<?php

namespace App\Filament\Resources\CardNumberResource\Pages;

use App\Filament\Resources\CardNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCardNumber extends ViewRecord
{
    protected static string $resource = CardNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
