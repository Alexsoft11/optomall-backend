<?php

namespace App\Filament\Resources\CachedProductResource\Pages;

use App\Filament\Resources\CachedProductResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCachedProducts extends ListRecords
{
    protected static string $resource = CachedProductResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(trans('all'))
                ->modifyQueryUsing(fn (Builder $query) => $query), // Keeps default sort from Resource
            'most_viewed' => Tab::make(trans('most_viewed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->reorder()->orderBy('views', 'desc')),
        ];
    }
}
