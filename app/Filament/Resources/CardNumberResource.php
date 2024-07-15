<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardNumberResource\Pages;
use App\Filament\Resources\CardNumberResource\RelationManagers;
use App\Models\CardNumber;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
                TextInput::make('card_number')->label('Karta raqami'),
                TextInput::make('money')->label('Pul'),
                Group::make()
                    ->schema([
                        Section::make('Buyurtma checklari')
                            ->schema([
                                Repeater::make('checks')->label('Cheklar')->relationship('checks')->schema([
                                    FileUpload::make('image')
                                        ->disk('public')
                                        ->label('Chek'),
                                ]),
                            ]),
                    ])->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('card_number')->label('Karta raqami'),
                TextColumn::make('money')->label('pul'),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCardNumbers::route('/'),
            'create' => Pages\CreateCardNumber::route('/create'),
            'edit' => Pages\EditCardNumber::route('/{record}/edit'),
        ];
    }
}
