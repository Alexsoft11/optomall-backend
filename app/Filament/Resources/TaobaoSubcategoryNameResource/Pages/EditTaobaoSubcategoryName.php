<?php

namespace App\Filament\Resources\TaobaoSubcategoryNameResource\Pages;

use App\Filament\Resources\TaobaoSubcategoryNameResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaobaoSubcategoryName extends EditRecord
{
    protected static string $resource = TaobaoSubcategoryNameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
