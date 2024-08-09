<?php

namespace App\Filament\Resources\HomePageProductResource\Pages;

use App\Filament\Resources\HomePageProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomePageProducts extends ListRecords
{
    protected static string $resource = HomePageProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
