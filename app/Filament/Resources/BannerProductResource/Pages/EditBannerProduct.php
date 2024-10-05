<?php

namespace App\Filament\Resources\BannerProductResource\Pages;

use App\Filament\Resources\BannerProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBannerProduct extends EditRecord
{
    protected static string $resource = BannerProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
