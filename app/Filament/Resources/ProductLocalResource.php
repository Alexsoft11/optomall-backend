<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductLocalResource\Pages;
use App\Filament\Resources\ProductLocalResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductLocalResource\RelationManagers\SkuPropsRelationManager;
use App\Filament\Resources\ProductLocalResource\RelationManagers\SkuRelationManager;
use App\Models\ProductLocal;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class ProductLocalResource extends Resource
{
    protected static ?string $model = ProductLocal::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required(),
                Textarea::make('description')->required(),
                TextInput::make('price')->numeric()->required(),
                Select::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])->required(),
                TextInput::make('category_id')->numeric()->required(),
                TextInput::make('brand')->required(),
                TextInput::make('quantity')->numeric()->required(),
                TextInput::make('sale_count')->numeric()->default(0),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('price')->sortable(),
                TextColumn::make('status')->sortable(),
                TextColumn::make('brand')->sortable(),
                TextColumn::make('quantity')->sortable(),
                TextColumn::make('sale_count')->sortable(),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SkuRelationManager::class,
            SkuPropsRelationManager::class,
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductLocals::route('/'),
            'create' => Pages\CreateProductLocal::route('/create'),
            'edit' => Pages\EditProductLocal::route('/{record}/edit'),
        ];
    }
}
