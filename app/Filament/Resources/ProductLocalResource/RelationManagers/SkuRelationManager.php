<?php

namespace App\Filament\Resources\ProductLocalResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class SkuRelationManager extends RelationManager
{
    protected static string $relationship = 'skus';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('sku_id')->sortable(),
                TextColumn::make('quantity')->sortable(),
                TextColumn::make('price')->sortable(),
                TextColumn::make('sale_count')->sortable(),
            ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('sku_id')->required(),
            TextInput::make('quantity')->numeric()->required(),
            TextInput::make('price')->numeric()->required(),
            TextInput::make('sale_count')->numeric(),
        ]);
    }
}
