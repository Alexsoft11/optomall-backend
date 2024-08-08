<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderSubStatus;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = "orders";

    protected static ?string $label = "orders";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        ToggleButtons::make('country')
                            ->options([
                                'uzbekistan' => 'Uzbekistan',
                                'china' => 'China'
                            ])
                            ->icons([
                                'uzbekistan' => 'heroicon-o-flag',
                                'china' => 'heroicon-o-flag'
                            ]),
                        Fieldset::make('Foydalanuvchi')->relationship('user')
                            ->schema([
                                TextInput::make('unique_id')->label('Id'),
                                TextInput::make('name')->label('Ism'),
                            ])->disabled(),
                        Fieldset::make('Buyurtma')->schema([
                            TextInput::make('unique_id')->label('Buyurtma IDsi')->disabled(),
                            TextInput::make('total_price')->label('Jami narxi')->disabled(),
                            TextInput::make('created_at')->label('Created At')->disabled(),
                            TextInput::make('updated_at')->label('Updated At')->disabled(),
                            Select::make('status')
                                ->label('Status')
                                ->options(OrderStatus::whereNotNull('name_uz')->pluck('name_uz', 'id'))
                                ->reactive(),
                            Toggle::make('is_paid'),
                        ]),
                        Section::make('Order items')
                            ->headerActions([
                                Action::make('reset')
                                    ->modalHeading('Are you sure?')
                                    ->modalDescription('All existing items will be removed from the order.')
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn (Forms\Set $set) => $set('items', [])),
                            ])
                            ->schema([
                                static::getItemsRepeater(),
                            ])
                            ->disabled(),
                        Fieldset::make('Foydalanuvchi')->relationship('user')
                            ->schema([
                                TextInput::make('last_name')->label('Familiyasi'),
                                TextInput::make('phone')->label('Telefon raqami'),
                                TextInput::make('email')->label('Pochta'),
                                TextInput::make('location')->label('Manzil linki'),
                                TextInput::make('region')->label('Viloyat'),
                                TextInput::make('district')->label('Tuman'),
                            ])->disabled(),
                        Group::make()
                            ->schema([
                                Section::make("Qo'shimcha")
                                    ->schema([
                                        TextInput::make('delivery_time')->label("Yetkazib berish vaqti"),
                                        TextInput::make('kg')->label("Kargo og'irligi"),
                                    ]),
                            ])
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),
                Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn (Order $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn (Order $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Order $record) => $record === null),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unique_id')
                    ->sortable()
                    ->searchable()
                    ->label('Order ID'),
                TextColumn::make('user.unique_id')->label('User ID')->sortable(),
                TextColumn::make('user.name')->label('Ismi')->sortable(),
                TextColumn::make('total_price')->label('Jami narx')->sortable(),
                TextColumn::make('getStatus.name_uz')
                    ->label('Status')
                    ->sortable()
                    ->searchable(),
                ColorColumn::make('status_color')->label('Status Rangi'),
                TextColumn::make('created_at')->label('Created At')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatus::pluck('name_uz', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('user_id')
                    ->options(User::pluck('name', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc'); // Set the default sorting here
    }

    public static function getRelations(): array
    {
        return [
            // Define any relationships here if needed
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->schema([
                TextInput::make('price')
                    ->label('Unit Price')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan([
                        'md' => 3,
                    ]),

                TextInput::make('color')->label('Rang')->columnSpan([
                    'md' => 3,
                ]),
                TextInput::make('size')->label("O'lcham")->columnSpan([
                    'md' => 3,
                ]),
                FileUpload::make('image_url')->disk('public')->columnSpan([
                    'lg' => 3,
                ]),
                TextInput::make('title')->columnSpan([
                    'md' => 4,
                ]),

                TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->columnSpan([
                        'md' => 2,
                    ])
                    ->required(),


            ])
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip('Open product')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);

                        // Ensure product_url exists
                        if (empty($itemData['product_url'])) {
                            return null;
                        }

                        return $itemData['product_url'] . '&skuId=' . $itemData['skuId'];
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['id'])),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->required();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            // 'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return trans('orders');
    }

    public static function getLabel(): string
    {
        return trans('orders');
    }
}
