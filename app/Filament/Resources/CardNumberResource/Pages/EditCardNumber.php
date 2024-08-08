<?php

namespace App\Filament\Resources\CardNumberResource\Pages;

use App\Filament\Resources\CardNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCardNumber extends EditRecord
{
    protected static string $resource = CardNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
