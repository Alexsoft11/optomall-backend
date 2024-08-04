<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaobaoCategoryResource\Pages;
use App\Filament\Resources\TaobaoCategoryResource\RelationManagers;
use App\Models\TaobaoCategory;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaobaoCategoryResource extends Resource   
{
    protected static ?string $model = TaobaoCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name_uz')->label('Name (UZ)')->nullable(),
                TextInput::make('name_en')->label('Name (EN)')->nullable(),
                TextInput::make('name_ru')->label('Name (RU)')->nullable(),
                Toggle::make('is_active')->label('Is Active')->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_uz')->label('Name (UZ)'),
                Tables\Columns\TextColumn::make('name_en')->label('Name (EN)'),
                Tables\Columns\TextColumn::make('name_ru')->label('Name (RU)'),
                ToggleColumn::make('is_active')->label('Is Active'),
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
            'index' => Pages\ListTaobaoCategories::route('/'),
            'create' => Pages\CreateTaobaoCategory::route('/create'),
            'edit' => Pages\EditTaobaoCategory::route('/{record}/edit'),
        ];
    }
}
