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

    protected static ?string $model = TaobaoSubcategoryName::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('taobao_category_id')
                    ->relationship('category', 'name_en')
                    ->label('Category')
                    ->required(),
                Toggle::make('is_active')->label('Is Active'),
                TextInput::make('unique')->label('Search'),
                TextInput::make('subcategory_name_uz')->label('Subcategory Name (UZ)')->nullable(),
                TextInput::make('subcategory_name_en')->label('Subcategory Name (EN)')->nullable(),
                TextInput::make('subcategory_name_ru')->label('Subcategory Name (RU)')->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name_en')->label('Category'),
                ToggleColumn::make('is_active')->label('Is Active'),
                Tables\Columns\TextColumn::make('subcategory_name_uz')->label('Subcategory Name (UZ)'),
                Tables\Columns\TextColumn::make('subcategory_name_en')->label('Subcategory Name (EN)'),
                Tables\Columns\TextColumn::make('subcategory_name_ru')->label('Subcategory Name (RU)'),
                Tables\Columns\TextColumn::make('unique')->label('Search'),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime(),
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
}
