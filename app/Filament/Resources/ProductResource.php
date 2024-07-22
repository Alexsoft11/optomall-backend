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
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\Toggle;
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

    protected static ?string $navigationLabel = "products";

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('category_id')
                    ->label('Mahsulot kategoriyasi')
                    ->relationship('category', 'name_uz')
                    ->required(),
                Forms\Components\TextInput::make('title_uz')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('title_ru')
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label('Narx')
                    ->numeric()
                    ->required(),
                TextInput::make('description_uz')
                    ->nullable(),
                TextInput::make('description_ru')
                    ->nullable(),
                TextInput::make('description_en')
                    ->nullable(),
                    Forms\Components\TextInput::make('rating')
                    ->numeric()
                    ->nullable(),
                    Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->nullable(),
                FileUpload::make('image')
                    ->label('Rasm')
                    ->directory('product-images')
                    ->image(),
                Repeater::make('pictures')
                    ->label('Rasmlar')
                    ->relationship('pictures')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url'),
                        Forms\Components\Toggle::make('is_main')->label('Main Picture'),
                    ]),
                Repeater::make('colors')
                    ->label('Ranglar')
                    ->relationship('colors')
                    ->schema([
                        Forms\Components\TextInput::make('value')->nullable(),
                        Forms\Components\FileUpload::make('image_url'),
                        Forms\Components\FileUpload::make('mini_image_url'),
                    ]),
                Repeater::make('sizes')
                    ->label("O'lchamlar")
                    ->relationship('sizes')
                    ->schema([
                        Forms\Components\TextInput::make('value')->nullable(),
                    ]),
                Repeater::make('compositions')
                    ->label("Tarkibiy qismlar")
                    ->relationship('compositions')
                    ->schema([
                        Forms\Components\TextInput::make('value')->nullable(),
                    ]),
                    Toggle::make('is_active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name_uz')->label('Category')->sortable()->searchable(),
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('price')->sortable()->searchable(),
                ImageColumn::make('image')->sortable()->label(trans('image')),
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

    public static function getNavigationLabel(): string
    {
        return trans('products');
    }
}
