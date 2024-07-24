<?php

namespace App\Filament\Resources\IncomeResource\Widgets;

use App\Models\Income;
use Filament\Widgets\Widget;

class TotalAmount extends Widget
{
    protected static string $view = 'filament.resources.income-resource.widgets.total-amount';

    protected function getViewData(): array
    {
        $totalAmount = Income::sum('amount');

        return [
            'totalAmount' => $totalAmount,
        ];
    }
}
