<?php

namespace App\Filament\Resources\OrderGroupResource\Pages;

use App\Filament\Resources\OrderGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderGroup extends EditRecord
{
    protected static string $resource = OrderGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
