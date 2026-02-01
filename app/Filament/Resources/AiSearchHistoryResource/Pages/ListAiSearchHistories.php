<?php

namespace App\Filament\Resources\AiSearchHistoryResource\Pages;

use App\Filament\Resources\AiSearchHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAiSearchHistories extends ListRecords
{
    protected static string $resource = AiSearchHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
