<?php

namespace App\Filament\Resources\CachedProductResource\Widgets;

use App\Models\CachedProduct;
use App\Models\HomePageProduct;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(trans('cached_products'), CachedProduct::count())
                ->description(trans('total_views') . ': ' . CachedProduct::sum('views'))
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make(trans('home_page'), HomePageProduct::count())
                ->color('info'),
        ];
    }
}
