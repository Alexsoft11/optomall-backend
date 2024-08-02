<?php

namespace App\Filament\Resources\TaobaoCategoryResource\Pages;

use App\Filament\Resources\TaobaoCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaobaoCategories extends ListRecords
{
    protected static string $resource = TaobaoCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
