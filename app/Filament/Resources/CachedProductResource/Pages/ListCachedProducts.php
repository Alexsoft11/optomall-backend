<?php

namespace App\Filament\Resources\CachedProductResource\Pages;

use App\Filament\Resources\CachedProductResource;
use Filament\Resources\Pages\ListRecords;

class ListCachedProducts extends ListRecords
{
    protected static string $resource = CachedProductResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
