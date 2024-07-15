<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\OrderStatus;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = "orders";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Foydalanuvchi')
                    ->schema([
                        TextInput::make('user_name')->label('Ism'),
                        TextInput::make('user_phone')->label('Telefon raqami'),
                        TextInput::make('location_url')->label('Manzil linki'),
                        TextInput::make('location_name')->label('Manzil nomi'),
                    ]),
                Fieldset::make('Buyurtma')->schema([
                    TextInput::make('id')->label('Buyurtma IDsi')->disabled(),
                    TextInput::make('total_price')->label('Jami narxi')->disabled(),
                    TextInput::make('created_at')->label('Created At')->disabled(),
                    TextInput::make('updated_at')->label('Updated At')->disabled(),
                ]),
                Group::make()
                    ->schema([
                        Section::make('Buyurtma mahsulotlari')
                            ->schema([
                                Repeater::make('items')
                                    ->label('Mahsulot')
                                    ->relationship('items')
                                    ->schema([
                                        TextInput::make('image_url')->label('Rasm'),
                                        TextInput::make('product_url')->label('Mahsulot linki'),
                                        TextInput::make('title')->label('Nomi'),
                                        TextInput::make('quantity')->label('Miqdori'),
                                        TextInput::make('price')->label('Narxi'),
                                        TextInput::make('color')->label('Rang'),
                                        TextInput::make('size')->label("O'lcham"),
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),
                Group::make()
                    ->schema([
                        Section::make('Buyurtma checklari')
                            ->schema([
                                Repeater::make('checks')->label('Cheklar')->relationship('checks')->schema([
                                    FileUpload::make('image')->disk('public')->label('Chek'),
                                ]),
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
                TextColumn::make('user_name')->label('Ismi')->sortable(),
                TextColumn::make('total_price')->label('Jami narx')->sortable(),
                TextColumn::make('status')->sortable(),
                TextColumn::make('created_at')->label('Created At')->sortable(),
                TextColumn::make('updated_at')->label('Updated At')->sortable(),
                TextColumn::make('status_color')->label('Status Rangi')->sortable(), // Add the status_color column
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatus::pluck('name', 'slug')->toArray()), // Fetch statuses from database
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('changeStatus')
                    ->label("Statusni o'zgartirish")
                    ->form([
                        Select::make('status')
                            ->options(OrderStatus::pluck('name', 'slug')->toArray()) // Fetch statuses from database
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $status = OrderStatus::where('slug', $data['status'])->get()->first();
                        if ($status) {
                            $record->update([
                                'status' => $status->slug,
                                'status_color' => $status->color, // Update the status_color field
                            ]);
                        }
                    })
                    ->modalHeading('Change Status')
                    ->button(),
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

    public static function getNavigationLabel(): string
    {
        return trans('orders');
    }
}
