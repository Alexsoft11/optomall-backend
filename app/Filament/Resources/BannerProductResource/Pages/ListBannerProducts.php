<?php

namespace App\Filament\Resources\BannerProductResource\Pages;

use App\Filament\Resources\BannerProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBannerProducts extends ListRecords
{
    protected static string $resource = BannerProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
