<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaobaoCategoryResource\Pages;
use App\Filament\Resources\TaobaoCategoryResource\RelationManagers;
use App\Models\TaobaoCategory;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
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
    protected static ?string $navigationGroup = "Taobao";

    protected static ?string $label = "taobao_categories";

    protected static ?string $navigationLabel = "taobao_categories";



    protected static ?string $model = TaobaoCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name_uz')->label(trans("title_uz"))->nullable(),
                TextInput::make('name_en')->label(trans("title_en"))->nullable(),
                TextInput::make('name_ru')->label(trans("title_ru"))->nullable(),
                FileUpload::make('image')
                    ->directory('category-images')
                    ->image()
                    ->label(trans('image'))
                    ->maxSize(10000)
                    ->nullable(),
                Toggle::make('is_active')->label(trans("is_active"))->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_uz')->label(trans("title_uz")),
                Tables\Columns\TextColumn::make('name_en')->label(trans("title_en")),
                Tables\Columns\TextColumn::make('name_ru')->label(trans("title_ru")),
                ToggleColumn::make('is_active')->label(trans("is_active")),
                Tables\Columns\TextColumn::make('created_at')->label(trans("created_at"))->dateTime(),
                // Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime(),
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

    public static function getLabel(): string
    {
        return trans('taobao_categories');
    }

    public static function getNavigationLabel(): string
    {
        return trans('taobao_categories');
    }
}
