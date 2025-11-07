<?php

namespace App\Filament\Resources\TaobaoSubcategoryResource\Pages;

use App\Filament\Resources\TaobaoSubcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaobaoSubcategory extends EditRecord
{
    protected static string $resource = TaobaoSubcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
