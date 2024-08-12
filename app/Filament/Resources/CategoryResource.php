<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{

    protected static ?string $navigationGroup = "products";

    protected static ?string $navigationLabel = "categories";

    protected static ?string $label = "categories";


    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-4';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name_uz')->required()->label(trans("title_uz")),
                TextInput::make('name_ru')->required()->label(trans("title_ru")),
                TextInput::make('name_en')->required()->label(trans("title_en")),
                FileUpload::make('image')->directory('category-images')->image()->disk('public')->label(trans("image")),
                FileUpload::make('image_mobile')->directory('category-images')->image()->disk('public')->label(trans("mobile_image")),
                Toggle::make('is_active')->label(trans("is_active")),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->searchable(),
                TextColumn::make('name_uz')->sortable()->searchable()->label(trans("title_uz")),
                TextColumn::make('name_ru')->sortable()->searchable()->label(trans("title_ru")),
                TextColumn::make('name_en')->sortable()->searchable()->label(trans("title_en")),
                ImageColumn::make('image')->sortable()->disk('public')->label(trans('image')),
                ImageColumn::make('image_mobile')->sortable()->disk('public')->label(trans("mobile_image")),
                ToggleColumn::make('is_active')->sortable()->label(trans("is_active")),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
            'view' => Pages\ViewCategory::route('/{record}'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return trans('categories');
    }
    public static function getLabel(): string
    {
        return trans('categories');
    }

    public static function getNavigationGroup(): string
    {
        return trans('products');
    }
}
