<?php

namespace App\Filament\Resources\IncomeResource\Widgets;

use App\Models\Income;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class IncomeStatsOverview extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $now = now();

        $lastMonthStart = $now->copy()->startOfMonth();
        $lastMonthEnd = $now->copy()->endOfMonth();

        $thisYearStart = $now->copy()->startOfYear();

        $lastMonthTotal = Income::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $thisYearTotal = Income::whereBetween('created_at', [$thisYearStart, $now])
            ->sum('amount');

        $allTimeTotal = Income::sum('amount');

        return [
            Stat::make('Joriy oy', number_format($lastMonthTotal, 2, '.', ' '))
                ->description($lastMonthStart->format('M Y'))
                ->descriptionIcon('heroicon-m-calendar-days'),

            Stat::make('Joriy yil', number_format($thisYearTotal, 2, '.', ' '))
                ->description($thisYearStart->year)
                ->descriptionIcon('heroicon-m-chart-bar'),

            Stat::make('Umumiy Savdo', number_format($allTimeTotal, 2, '.', ' '))
                ->description('Barcha vaqtlar')
                ->descriptionIcon('heroicon-m-banknotes'),
        ];
    }
}
