<?php

namespace App\Filament\Resources\OrderSubStatusResource\Pages;

use App\Filament\Resources\OrderSubStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderSubStatuses extends ListRecords
{
    protected static string $resource = OrderSubStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
