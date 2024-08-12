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
    protected static ?string $navigationGroup = "Buyurtmalar";
    protected static ?string $label = "order_status";

    protected static ?string $model = OrderStatus::class;



    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name_uz')->label(trans("title_uz")),
                TextInput::make('name_en')->label(trans("title_en")),
                TextInput::make('name_ru')->label(trans("title_ru")),
                TextInput::make('message_uz')->label(trans("description_uz")),
                TextInput::make('message_en')->label(trans("description_en")),
                TextInput::make('message_ru')->label(trans("description_ru")),
                ColorPicker::make('color')->label(trans("colors")),
                Select::make('type')->label(trans("type"))->options([
                    'active' => 'Aktiv',
                    'in_verification' => 'Tekshirilmoqda',
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_uz')->label(trans("name")),
                // TextColumn::make('message_uz')->label('Xabar'),
                ColorColumn::make('color')->label(trans("colors")),
                TextColumn::make('type')->label(trans("type")),
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
