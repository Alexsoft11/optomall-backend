<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderStatusResource\Pages;
use App\Filament\Resources\OrderStatusResource\RelationManagers;
use App\Models\OrderStatus;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderStatusResource extends Resource
{
    protected static ?string $model = OrderStatus::class;

    protected static ?string $label = "order_status";


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name_uz')->label('Nomi uz'),
                TextInput::make('name_en')->label('Nomi en'),
                TextInput::make('name_ru')->label('Nomi ru'),
                TextInput::make('message_uz')->label('Xabar_uz'),
                TextInput::make('message_en')->label('Xabar_en'),
                TextInput::make('message_ru')->label('Xabar_ru'),
                ColorPicker::make('color')->label('Rangi'),
                Select::make('type')->label('Turi')->options([
                    'active' => 'Aktiv',
                    'in_verification' => 'Tekshirilmoqda',
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_uz')->label('Nomi'),
                // TextColumn::make('message_uz')->label('Xabar'),
                ColorColumn::make('color')->label('Rangi'),
                TextColumn::make('type')->label('Turi'),
            ])
            ->filters([
                // Add your filters here if needed
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
            // Define your relations here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderStatuses::route('/'),
            'create' => Pages\CreateOrderStatus::route('/create'),
            'edit' => Pages\EditOrderStatus::route('/{record}/edit'),
        ];
    }


    public static function getNavigationLabel(): string
    {
        return trans('order_status');
    }

    public static function getLabel(): string
    {
        return trans('order_status');
    }
}
