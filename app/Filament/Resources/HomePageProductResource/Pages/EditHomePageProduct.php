<?php

namespace App\Filament\Resources\HomePageProductResource\Pages;

use App\Filament\Resources\HomePageProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomePageProduct extends EditRecord
{
    protected static string $resource = HomePageProductResource::class;

    // In Pages/EditHomePageProduct.php
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
