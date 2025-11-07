<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExchangesResource\Pages;
use App\Filament\Resources\ExchangesResource\RelationManagers;
use App\Models\Exchanges;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExchangesResource extends Resource
{

        protected static ?string $navigationGroup = "Buyurtmalar";

    protected static ?string $label = "exchanges";

    protected static ?string $navigationLabel = "exchanges";

    protected static ?string $model = Exchanges::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('currency')
                ->label(trans("currency")),
                Forms\Components\TextInput::make('amount')
                ->label(trans("amount")),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('currency')->label(trans("currency")),
                Tables\Columns\TextColumn::make('amount')->label(trans("amount")),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListExchanges::route('/'),
            'create' => Pages\CreateExchanges::route('/create'),
            'edit' => Pages\EditExchanges::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return trans('exchanges');
    }
    public static function getLabel(): string
    {
        return trans('exchanges');
    }

    public static function getNavigationGroup(): string
    {
        return trans('products');
    }
}
