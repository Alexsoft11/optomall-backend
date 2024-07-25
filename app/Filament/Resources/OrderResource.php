<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderSubStatus;
use App\Models\Product;
// use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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

    protected static ?string $navigationLabel = "orders";

    protected static ?string $label = "orders";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
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
                                TextInput::make('last_name')->label('Familiyasi'),
                                TextInput::make('phone')->label('Telefon raqami'),
                                TextInput::make('location')->label('Manzil linki'),
                                // TextInput::make('location_name')->label('Manzil nomi'),
                                TextInput::make('region')->label('Viloyat'),
                                TextInput::make('district')->label('Tuman'),
                                TextInput::make('street')->label("Ko'cha"),
                                TextInput::make('house')->label("Uy"),
                                TextInput::make('post_index')->label("Zip kod"),
                            ])->disabled(),
                        Fieldset::make('Buyurtma')->schema([
                            TextInput::make('id')->label('Buyurtma IDsi')->disabled(),
                            TextInput::make('total_price')->label('Jami narxi')->disabled(),
                            TextInput::make('created_at')->label('Created At')->disabled(),
                            TextInput::make('updated_at')->label('Updated At')->disabled(),
                            Select::make('status')
                                ->label('Status')
                                ->options(OrderStatus::all()->pluck('name_uz', 'id'))
                                ->reactive(),
                            Select::make('substatus')
                                ->label('Sub Status')
                                ->options(OrderSubStatus::all()->pluck('name_uz', 'id'))
                                ->reactive()
                        ]),
                        Forms\Components\Section::make('Order items')
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
                            ]),
                        Group::make()
                            ->schema([
                                Section::make('Buyurtma checklari')
                                    ->schema([
                                        Repeater::make('checks')->label('Cheklar')->relationship('checks')->schema([
                                            FileUpload::make('image')->disk('public')->label('Chek'),
                                        ]),
                                    ]),
                            ])
                            ->disabled()
                            ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),
                        Group::make()
                            ->schema([
                                Section::make("Qo'shimcha")
                                    ->schema([
                                        // TextInput::make('substatus_uz')->label('Sub status uz'),
                                        // TextInput::make('substatus_ru')->label('Sub status ru'),
                                        // TextInput::make('substatus_en')->label('Sub status en'),
                                        // ColorPicker::make('substatus_color')->label('Sub status rangi'),
                                        Select::make('substatus_slug')
                                            ->label('Substatus slug')
                                            ->options([
                                                'send_check_again' => 'Chekni qayta yuboring',
                                                "wait_for_pay_cargo" => "Kargo uchun to'lov kutilmoqda"
                                            ]),
                                        TextInput::make('delivery_time')->label("Yetkazib berish vaqti"),
                                        TextInput::make('kg')->label("Kargo og'irligi"),
                                    ]),
                            ])
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),
                Forms\Components\Section::make()
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
                TextColumn::make('id')->sortable(),
                TextColumn::make('user_name')->label('Ismi')->sortable(),
                TextColumn::make('total_price')->label('Jami narx')->sortable(),
                TextColumn::make('getStatus.name_uz')
                    ->label('Status')
                    ->sortable()
                    ->searchable(),
                // TextColumn::make('getSubStatus.name_uz')
                //     ->label('Sub Status')
                //     ->sortable()
                //     ->searchable(),
                TextColumn::make('created_at')->label('Created At')->sortable(),
                TextColumn::make('updated_at')->label('Updated At')->sortable(),
                TextColumn::make('status_color')->label('Status Rangi')->sortable(), // Add the status_color column
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatus::pluck('name_uz', 'slug')->toArray()), // Fetch statuses from database
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\Action::make('changeStatus')
                //     ->label("Statusni o'zgartirish")
                //     ->form([
                //         Select::make('status')
                //             ->options(OrderStatus::pluck('name_uz', 'slug')->toArray()) // Fetch statuses from database
                //             ->required(),
                //     ])
                //     ->action(function ($record, $data) {
                //         $status = OrderStatus::where('slug', $data['status'])->get()->first();
                //         if ($status) {
                //             $record->update([
                //                 'status' => $status->slug,
                //                 'status_color' => $status->color, // Update the status_color field
                //             ]);
                //         }
                //     })
                //     ->modalHeading('Change Status')
                //     ->button(),
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

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->schema([
                FileUpload::make('image_url')->disk('public')->columnSpan([
                    'lg' => 10,
                ]),
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(Product::query()->pluck('title_uz', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('unit_price', Product::find($state)?->price ?? 0))
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->columnSpan([
                        'md' => 2,
                    ])
                    ->required(),

                Forms\Components\TextInput::make('price')
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
            ])
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip('Open product')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);

                        $product = Product::find($itemData['id']);

                        if (!$product) {
                            return null;
                        }

                        return ProductResource::getUrl('edit', ['record' => $product]);
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['id'])),
            ])
            // ->orderColumn('sort')
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
            'view' => Pages\ViewOrder::route('/{record}'),
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
