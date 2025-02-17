<?php

namespace App\Filament\Resources\ProductLocalResource\Pages;

use App\Filament\Resources\ProductLocalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductLocal extends EditRecord
{
    protected static string $resource = ProductLocalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
