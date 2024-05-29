<?php

namespace App\Filament\Resources\SocialsResource\Pages;

use App\Filament\Resources\SocialsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocials extends EditRecord
{
    protected static string $resource = SocialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
