<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaobaoSubcategoryResource\Pages;
use App\Filament\Resources\TaobaoSubcategoryResource\RelationManagers;
use App\Models\TaobaoSubcategory;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaobaoSubcategoryResource extends Resource
{
    protected static ?string $navigationGroup = "Taobao";

    protected static ?string $label = "taobao_sub_categories";

    protected static ?string $navigationLabel = "taobao_sub_categories";

    protected static ?string $model = TaobaoSubcategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('taobao_subcategory_name_id')
                    ->relationship('subcategoryName', 'subcategory_name_en')
                    ->label(trans("name"))
                    ->required(),
                Toggle::make('is_show')->label(trans("is_active")),
                TextInput::make('subcategory_uz')->label(trans("sub_category_uz"))->nullable(),
                TextInput::make('subcategory_en')->label(trans("sub_category_en"))->nullable(),
                TextInput::make('subcategory_ru')->label(trans("sub_category_ru"))->nullable(),
                TextInput::make('unique')->label(trans("search"))->nullable(),
                Repeater::make('hints')
                    ->relationship('hints')
                    ->label(trans("hints")) 
                    ->schema([
                        TextInput::make('name')->required()->label(trans("hint")),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subcategoryName.subcategory_name_en')->label(trans("name")),
                ToggleColumn::make('is_show')->label(trans("is_show")),
                Tables\Columns\TextColumn::make('subcategory_uz')->label(trans("sub_category_uz")),
                Tables\Columns\TextColumn::make('subcategory_en')->label(trans("sub_category_en")),
                Tables\Columns\TextColumn::make('subcategory_ru')->label(trans("sub_category_ru")),
                TextColumn::make('unique')->label(trans("search")),
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
            'index' => Pages\ListTaobaoSubcategories::route('/'),
            'create' => Pages\CreateTaobaoSubcategory::route('/create'),
            'edit' => Pages\EditTaobaoSubcategory::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return trans('taobao_sub_categories');
    }

    public static function getNavigationLabel(): string
    {
        return trans('taobao_sub_categories');
    }
}
