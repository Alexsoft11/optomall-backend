<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaobaoSubcategoryNameResource\Pages;
use App\Filament\Resources\TaobaoSubcategoryNameResource\RelationManagers;
use App\Models\TaobaoSubcategoryName;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaobaoSubcategoryNameResource extends Resource
{
    protected static ?string $navigationGroup = "Taobao";

    protected static ?string $label = "taobao_sub_categories_name";

    protected static ?string $navigationLabel = "taobao_sub_categories_name";

    protected static ?string $model = TaobaoSubcategoryName::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('taobao_category_id')
                    ->relationship('category', 'name_en')
                    ->label(trans("category"))
                    ->required(),
                Toggle::make('is_active')->label(trans("is_active")),
                TextInput::make('unique')->label(trans("search")),
                TextInput::make('subcategory_name_uz')->label(trans("sub_category_uz_name"))->nullable(),
                TextInput::make('subcategory_name_en')->label(trans("sub_category_en_name"))->nullable(),
                TextInput::make('subcategory_name_ru')->label(trans("sub_category_ru_name"))->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name_en')->label(trans("category")),
                ToggleColumn::make('is_active')->label(trans("is_active")),
                Tables\Columns\TextColumn::make('subcategory_name_uz')->label(trans("sub_category_uz_name")),
                Tables\Columns\TextColumn::make('subcategory_name_en')->label(trans("sub_category_en_name")),
                Tables\Columns\TextColumn::make('subcategory_name_ru')->label(trans("sub_category_ru_name")),
                Tables\Columns\TextColumn::make('unique')->label(trans("search")),
                Tables\Columns\TextColumn::make('created_at')->label(trans("created_at"))->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->label(trans("updated_at"))->dateTime(),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaobaoSubcategoryNames::route('/'),
            'create' => Pages\CreateTaobaoSubcategoryName::route('/create'),
            'edit' => Pages\EditTaobaoSubcategoryName::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return trans('taobao_sub_categories_name');
    }

    public static function getNavigationLabel(): string
    {
        return trans('taobao_sub_categories_name');
    }
}
