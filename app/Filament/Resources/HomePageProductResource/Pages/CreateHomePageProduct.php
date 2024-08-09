<?php

namespace App\Filament\Resources\HomePageProductResource\Pages;

use App\Filament\Resources\HomePageProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHomePageProduct extends CreateRecord
{
    protected static string $resource = HomePageProductResource::class;
}
