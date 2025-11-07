<?php

namespace App\Filament\Resources\TaobaoCategoryResource\Pages;

use App\Filament\Resources\TaobaoCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaobaoCategory extends EditRecord
{
    protected static string $resource = TaobaoCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
