<?php

namespace App\Filament\Resources\TaobaoSubcategoryResource\Pages;

use App\Filament\Resources\TaobaoSubcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaobaoSubcategories extends ListRecords
{
    protected static string $resource = TaobaoSubcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
