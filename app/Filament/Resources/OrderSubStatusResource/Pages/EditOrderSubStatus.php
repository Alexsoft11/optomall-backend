<?php

namespace App\Filament\Resources\OrderSubStatusResource\Pages;

use App\Filament\Resources\OrderSubStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderSubStatus extends EditRecord
{
    protected static string $resource = OrderSubStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
