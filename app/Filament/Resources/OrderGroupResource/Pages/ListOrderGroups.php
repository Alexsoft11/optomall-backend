<?php

namespace App\Filament\Resources\OrderGroupResource\Pages;

use App\Filament\Resources\OrderGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderGroups extends ListRecords
{
    protected static string $resource = OrderGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
