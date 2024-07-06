<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{

    protected static ?string $navigationGroup = "Mahsulotlar";

    protected static ?string $navigationLabel = "Mahsulotlar";

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_category_id')
                    ->label('Mahsulot kategoriyasi')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('Sarlavha')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label('Narx')
                    ->numeric()
                    ->required(),
                FileUpload::make('image')
                    ->label('Rasm')
                    ->directory('product-images')
                    ->image(),
                Forms\Components\TextInput::make('location')
                    ->label('Hudud')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('product_url')
                    ->label('Mahsulot linki')
                    ->required()
                    ->url()
                    ->maxLength(255),
                Repeater::make('pictures')
                    ->label('Rasmlar')
                    ->relationship('pictures')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url'),
                        Forms\Components\Toggle::make('is_main')->label('Main Picture'),
                    ]),
                Repeater::make('videos')
                    ->label('Videolar')
                    ->relationship('videos')
                    ->schema([
                        Forms\Components\FileUpload::make('preview_url'),
                        Forms\Components\TextInput::make('url'),
                    ]),
                Repeater::make('colors')
                    ->label('Ranglar')
                    ->relationship('colors')
                    ->schema([
                        Hidden::make('property_name')->default('rang'),
                        Forms\Components\TextInput::make('value')->required(),
                        Forms\Components\FileUpload::make('image_url'),
                        Forms\Components\FileUpload::make('mini_image_url'),
                    ]),
                Repeater::make('sizes')
                    ->label("O'lchamlar")
                    ->relationship('sizes')
                    ->schema([
                        Hidden::make('property_name')->default("o'lcham"),
                        Forms\Components\TextInput::make('value')->required(),
                    ]),
                Repeater::make('compositions')
                    ->label("Tarkibiy qismlar")
                    ->relationship('compositions')
                    ->schema([
                        Hidden::make('property_name')->default('Moddiy tarkibiy qism'),
                        Forms\Components\TextInput::make('value')->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')->label('Category')->sortable()->searchable(),
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('price')->sortable()->searchable(),
                ImageColumn::make('image')->sortable(),
                TextColumn::make('location')->sortable()->searchable(),
                TextColumn::make('product_url')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'view' => Pages\ShowProduct::route('/{record}'),
        ];
    }
}
