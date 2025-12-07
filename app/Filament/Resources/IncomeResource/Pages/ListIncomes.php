<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use App\Filament\Resources\IncomeResource\Widgets\TotalAmount;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncomes extends ListRecords
{
    protected static string $resource = IncomeResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }

    protected function getHeaderWidgets(): array
    {
        return [
            TotalAmount::class,
        ];
    }
    
    protected function getTableRecordUrl($record): ?string
    {
        return self::getResource()::getUrl('view', ['record' => $record]);
    }


    public function getTitle(): string
    {
        return trans('reciepts');
    }
}
