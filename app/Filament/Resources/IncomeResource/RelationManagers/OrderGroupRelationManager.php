<?php

namespace App\Filament\Resources\IncomeResource\RelationManagers;

use App\Filament\Resources\OrderGroupResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderGroupRelationManager extends RelationManager
{
    protected static string $relationship = 'orderGroup';

    protected static ?string $title = 'Order group';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total price')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([]) // no create
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->url(fn ($record) => OrderGroupResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]); // no bulk
    }
}
