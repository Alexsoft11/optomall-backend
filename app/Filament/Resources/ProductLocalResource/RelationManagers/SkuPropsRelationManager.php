<?php

namespace App\Filament\Resources\ProductLocalResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;

class SkuPropsRelationManager extends RelationManager
{
    protected static string $relationship = 'sku_props';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('prop_name')
                ->required()
                ->label('Property name')
                ->maxLength(255),
            Repeater::make('sku_values')
                ->relationship('sku_values')
                ->schema([
                    TextInput::make('name')
                        ->label('Property value')
                        ->required(),
                    TextInput::make('imageUrl')
                        ->label('Image URL')
                        ->nullable(),
                ])
                ->addActionLabel('Add SKU Value')
                ->columns(2),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('prop_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sku_values.name')
                    ->label('Property Values')
                    ->listWithLineBreaks()
                    ->limitList(3),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
