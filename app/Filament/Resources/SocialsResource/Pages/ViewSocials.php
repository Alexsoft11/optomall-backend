<?php

namespace App\Filament\Resources\SocialsResource\Pages;

use App\Filament\Resources\SocialsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSocials extends ViewRecord
{
    protected static string $resource = SocialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
