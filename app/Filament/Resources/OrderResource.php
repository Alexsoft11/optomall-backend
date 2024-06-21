<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\Modal\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('User')
                    ->relationship('user')
                    ->schema([
                        TextInput::make('id'),
                        TextInput::make('name'),
                        TextInput::make('email'),
                        TextInput::make('phone'),
                    ]),

                Fieldset::make('Order')->schema([
                    TextInput::make('id')->label('Order ID')->disabled(),
                    TextInput::make('total_price')->label('Total Price')->disabled(),
                    ToggleButtons::make('status')
                        ->inline()
                        ->options(OrderStatus::class)
                        ->required(),
                    TextInput::make('created_at')->label('Created At')->disabled(),
                    TextInput::make('updated_at')->label('Updated At')->disabled(),
                ]),
                Group::make()
                    ->schema([
                        Section::make('Order items')
                            ->schema([
                                Repeater::make('items')->label('item')->relationship('items')->schema([
                                    TextInput::make('quantity'),
                                    TextInput::make('price'),
                                    TextInput::make('color_id'),
                                    TextInput::make('size_id'),
                                    TextInput::make('composition_id'),
                                ])
                            ]),
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.name')->label('User')->sortable(),
                TextColumn::make('total_price')->label('Total Price')->sortable(),
                TextColumn::make('status')->sortable(),
                TextColumn::make('created_at')->label('Created At')->sortable(),
                TextColumn::make('updated_at')->label('Updated At')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'on_hold' => 'On Hold',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'returned' => 'Returned',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define any relationships here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
