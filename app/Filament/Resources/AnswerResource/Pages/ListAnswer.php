<?php

namespace App\Filament\Resources\AnswerResource\Pages;

use App\Filament\Resources\AnswerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnswer extends ListRecords
{
    protected static string $resource = AnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(trans('create')),
        ];
    }

    public function getTitle(): string
    {
        return trans('answers');
    }
}
