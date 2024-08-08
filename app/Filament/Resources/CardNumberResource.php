<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardNumberResource\Pages;
use App\Filament\Resources\CardNumberResource\RelationManagers;
use App\Models\CardNumber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CardNumberResource extends Resource
{
    protected static ?string $model = CardNumber::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('card_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('money')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255)
                    ->default('order'),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('card_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('money')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCardNumbers::route('/'),
            'create' => Pages\CreateCardNumber::route('/create'),
            'view' => Pages\ViewCardNumber::route('/{record}'),
            'edit' => Pages\EditCardNumber::route('/{record}/edit'),
        ];
    }
}
