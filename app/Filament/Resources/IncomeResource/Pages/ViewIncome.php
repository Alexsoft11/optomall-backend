<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIncome extends ViewRecord
{
    protected static string $resource = IncomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading(trans('delete_income'))
                ->modalDescription(trans('delete_income_confirm'))
                ->modalSubmitActionLabel(trans('delete_income_confirm_yes')),
        ];
    }
}
