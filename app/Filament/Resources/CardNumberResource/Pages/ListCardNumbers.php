<?php

namespace App\Filament\Resources\CardNumberResource\Pages;

use App\Filament\Resources\CardNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCardNumbers extends ListRecords
{
    protected static string $resource = CardNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(trans('create')),
        ];
    }

    public function getTitle(): string
    {
        return trans('card_number');
    }
}
