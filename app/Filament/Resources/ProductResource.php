<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('product_category_id')
                    ->label('Product Category')
                    ->options(ProductCategory::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->directory('product-images'),
                Forms\Components\TextInput::make('sell_count')
                    ->label('Sell Count')
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->nullable(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('discount')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('new_price')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Repeater::make('rating')
                    ->schema([
                        Forms\Components\TextInput::make('key'),
                        Forms\Components\TextInput::make('value'),
                    ])
                    ->nullable(),
                Forms\Components\Repeater::make('features')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Key'),
                        Forms\Components\FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->directory('feature-images'),
                    ])
                    ->nullable(),
                Forms\Components\Repeater::make('comments')
                    ->schema([
                        Forms\Components\TextInput::make('author'),
                        Forms\Components\Textarea::make('content'),
                    ])
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('productCategory.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sell_count')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('new_price')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                // Define your filters here
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define your relations here
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
