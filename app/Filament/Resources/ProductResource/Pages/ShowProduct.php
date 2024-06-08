<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ShowProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            // Add any widgets you want to display in the header
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                TextInput::make('title')->disabled(),
                TextInput::make('price')->disabled(),
                TextInput::make('image')->disabled(),
                TextInput::make('location')->disabled(),
                TextInput::make('product_url')->disabled(),
                Select::make('product_category_id')
                    ->relationship('category', 'name')
                    ->disabled(),
            ])->columns(2),

            Card::make([
                Repeater::make('pictures')
                    ->relationship('pictures')
                    ->schema([
                        TextInput::make('image_url')->disabled(),
                        Toggle::make('is_main')->disabled()->label('Main Picture'),
                    ]),
            ])->columns(1)->label('Pictures'),

            Card::make([
                Repeater::make('videos')
                    ->relationship('videos')
                    ->schema([
                        TextInput::make('preview_url')->disabled(),
                        TextInput::make('url')->disabled(),
                    ]),
            ])->columns(1)->label('Videos'),

            Card::make([
                Repeater::make('colors')
                    ->relationship('colors')
                    ->schema([
                        TextInput::make('property_name')->disabled(),
                        TextInput::make('value')->disabled(),
                        TextInput::make('image_url')->disabled(),
                        TextInput::make('mini_image_url')->disabled(),
                    ]),
            ])->columns(1)->label('Colors'),

            Card::make([
                Repeater::make('sizes')
                    ->relationship('sizes')
                    ->schema([
                        TextInput::make('property_name')->disabled(),
                        TextInput::make('value')->disabled(),
                        TextInput::make('image_url')->disabled(),
                        TextInput::make('mini_image_url')->disabled(),
                    ]),
            ])->columns(1)->label('sizes'),

            Card::make([
                Repeater::make('compositions')
                    ->relationship('compositions')
                    ->schema([
                        TextInput::make('property_name')->disabled(),
                        TextInput::make('value')->disabled(),
                        TextInput::make('image_url')->disabled(),
                        TextInput::make('mini_image_url')->disabled(),
                    ]),
            ])->columns(1)->label('compositions'),
        ];
    }
}
