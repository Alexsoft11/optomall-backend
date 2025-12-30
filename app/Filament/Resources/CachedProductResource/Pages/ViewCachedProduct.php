<?php

namespace App\Filament\Resources\CachedProductResource\Pages;

use App\Filament\Resources\CachedProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCachedProduct extends ViewRecord
{
    protected static string $resource = CachedProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_on_site')
                ->label('View on Site')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => "https://optomall.uz/{$this->record->language}/product/{$this->record->external_id}?provider={$this->record->provider}")
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
