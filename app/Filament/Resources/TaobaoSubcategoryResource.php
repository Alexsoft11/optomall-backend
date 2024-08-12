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

    protected static ?string $model = TaobaoSubcategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('taobao_subcategory_name_id')
                    ->relationship('subcategoryName', 'subcategory_name_en')
                    ->label('Subcategory Name')
                    ->required(),
                Toggle::make('is_show')->label('Is Active'),
                TextInput::make('subcategory_uz')->label('Subcategory (UZ)')->nullable(),
                TextInput::make('subcategory_en')->label('Subcategory (EN)')->nullable(),
                TextInput::make('subcategory_ru')->label('Subcategory (RU)')->nullable(),
                TextInput::make('unique')->label('Search')->nullable(),
                Repeater::make('hints')
                    ->relationship('hints') // Ensure this matches the relationship name in the model
                    ->schema([
                        TextInput::make('name')->required()->label('Hint name'),
                    ])
                    ->label('Hints')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subcategoryName.subcategory_name_en')->label('Subcategory Name'),
                ToggleColumn::make('is_show')->label('Is show'),
                Tables\Columns\TextColumn::make('subcategory_uz')->label('Subcategory (UZ)'),
                Tables\Columns\TextColumn::make('subcategory_en')->label('Subcategory (EN)'),
                Tables\Columns\TextColumn::make('subcategory_ru')->label('Subcategory (RU)'),
                TextColumn::make('unique')->label('Search'),
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
            'index' => Pages\ListTaobaoSubcategories::route('/'),
            'create' => Pages\CreateTaobaoSubcategory::route('/create'),
            'edit' => Pages\EditTaobaoSubcategory::route('/{record}/edit'),
        ];
    }
}
