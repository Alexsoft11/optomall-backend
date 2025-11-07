<?php

namespace App\Filament\Resources\SocialsResource\Pages;

use App\Filament\Resources\SocialsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSocials extends ListRecords
{
    protected static string $resource = SocialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(trans('create')),
        ];
    }

    public function getTitle(): string
    {
        return trans('socials');
    }
}
