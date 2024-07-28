<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderSubStatusResource\Pages;
use App\Filament\Resources\OrderSubStatusResource\RelationManagers;
use App\Models\OrderSubStatus;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderSubStatusResource extends Resource
{
    protected static ?string $model = OrderSubStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name_uz')->label('Nomi uz'),
                TextInput::make('name_en')->label('Nomi en'),
                TextInput::make('name_ru')->label('Nomi ru'),
                ColorPicker::make('color')->label('Rangi'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_uz')->label('Nomi'),
                ColorColumn::make('color')->label('Rangi'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrderSubStatuses::route('/'),
            'create' => Pages\CreateOrderSubStatus::route('/create'),
            'edit' => Pages\EditOrderSubStatus::route('/{record}/edit'),
        ];
    }
}
