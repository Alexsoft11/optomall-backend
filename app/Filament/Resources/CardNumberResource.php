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

class CardNumberResource extends Resource
{
    protected static ?string $model = CardNumber::class;

    protected static ?string $label = "card_number";


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('is_active')->label(trans('is_active')),
                TextInput::make('card_number')->label(trans('card_number')),
                TextInput::make('money')->label(trans('money')),
                Select::make('type')->label(trans('type'))->options([
                    'order' => trans('order'),
                    'cargo' => trans('cargo'),
                ]),
                Group::make()
                    ->schema([
                        Section::make(trans('order_checks'))
                            ->schema([
                                Repeater::make('checks')->label(trans('checks'))->relationship('checks')->schema([
                                    FileUpload::make('image')
                                        ->disk('public')
                                        ->label(trans('check')),
                                ]),
                            ]),
                    ])->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('card_number')->label(trans('card_number')),
                TextColumn::make('money')->label(trans('money')),
                ToggleColumn::make('is_active')->label(trans('is_active')),
                TextColumn::make('type')->label(trans('type')),
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

    
    public static function getNavigationLabel(): string
    {
        return trans('card_number');
    }
    public static function getLabel(): string
    {
        return trans('card_number');
    }
}
