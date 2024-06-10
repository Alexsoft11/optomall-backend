<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                TextInput::make('id')->label('Order ID')->disabled(),
                TextInput::make('user.name')->label('User')->disabled(),
                TextInput::make('total_price')->label('Total Price')->disabled(),
                TextInput::make('status')->label('Status')->disabled(),
                TextInput::make('created_at')->label('Created At')->disabled(),
                TextInput::make('updated_at')->label('Updated At')->disabled(),
            ]),
            Card::make([
                Repeater::make('items')
                    ->relationship('items')
                    ->schema([
                        TextInput::make('product.title')->label('Product')->disabled(),
                        TextInput::make('product.colors.value')->label('Color')->disabled(),
                        TextInput::make('product.sizes.value')->label('Size')->disabled(),
                        TextInput::make('quantity')->label('Quantity')->disabled(),
                        TextInput::make('price')->label('Price')->disabled(),
                    ])
                    ->disableItemMovement()
                    ->disableItemDeletion()
                    ->disabled()
            ])->label('Order Items')
        ];
    }
}
