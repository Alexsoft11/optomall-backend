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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    // protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    // protected static ?string $navigationLabel = "orders";

    // protected static ?string $label = "orders";

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
                                TextInput::make('name')->label(trans("ism")),
                            ])->disabled(),
                        Fieldset::make('Buyurtma')->schema([
                            TextInput::make('unique_id')->label(trans("order_id"))->disabled(),
                            TextInput::make('total_price')->label(trans("total_amount"))->disabled(),
                            TextInput::make('created_at')->label(trans("created_at"))->disabled(),
                            TextInput::make('updated_at')->label('Updated At')->disabled(),

                        ]),
                        Section::make(trans("orders"))
                            ->label(trans("orders"))
                            ->headerActions([
                                Action::make('reset')
                                    ->modalHeading('Are you sure?')
                                    ->modalDescription('All existing items will be removed from the order.')
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn(Forms\Set $set) => $set('items', [])),
                            ])
                            ->schema([
                                static::getItemsRepeater(),
                            ])
                            ->disabled(),
                        Fieldset::make('Foydalanuvchi')->relationship('user')
                            ->schema([
                                TextInput::make('last_name')->label(trans("last_name")),
                                TextInput::make('phone')->label(trans("phone")),
                                TextInput::make('email')->label(trans("email")),
                                // TextInput::make('location')->label(trans("adress")),
                                TextInput::make('region')->label(trans("region")),
                                TextInput::make('district')->label(trans("district")),
                            ])->disabled(),
                        Group::make()
                            ->schema([
                                Section::make(trans("additional"))
                                    ->label(trans("additional"))
                                    ->schema([
                                        TextInput::make('delivery_time')->label(trans("delivery_time")),
                                        TextInput::make('kg')->label(trans("cargo_weight")),
                                    ]),
                            ]),
                        Select::make('status')
                            ->label(trans("status"))
                            ->options(OrderStatus::whereNotNull('name_uz')->pluck('name_uz', 'id'))
                            ->reactive(),
                        Toggle::make('is_paid'),
                    ])->columnSpan(['lg' => fn(?Order $record) => $record === null ? 3 : 2]),
                Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label(trans("created_at"))
                            ->content(fn(Order $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn(Order $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?Order $record) => $record === null),
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
                ColorColumn::make('getStatus.color')->label('Status Rangi'),
                TextColumn::make('created_at')->label('Created At')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Sort by status')
                    ->options(
                        OrderStatus::pluck('name_uz', 'id')->mapWithKeys(function ($item, $key) {
                            return [(string) $key => (string) $item];
                        })->toArray()
                    ),
                Tables\Filters\SelectFilter::make('user_id')->label('Sort by user')
                    ->options(
                        User::pluck('name', 'id')->mapWithKeys(function ($item, $key) {
                            return [(string) $key => (string) $item];
                        })->toArray()
                    ),
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
        $locale = App::getLocale();
        // dd($locale);
        return Repeater::make('items')
            ->relationship()
            ->schema([
                // First Column: Image
                Forms\Components\View::make('image_url')
                    ->columnSpan(4)
                    ->label('Image Preview')
                    ->view('filament.components.image'),

                // Second Column: Other Fields
                Group::make()
                    ->schema([
                        Textarea::make('title')
                            ->label(trans("Name"))
                            ->placeholder('Product title')
                            ->required()
                            ->columnSpan(12),

                        TextInput::make('price')
                            ->label(trans("Price"))
                            ->numeric()
                            ->placeholder('Enter price')
                            ->disabled()
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('delivery_price')
                            ->label('Delivery' . trans("Price"))
                            ->numeric()
                            ->placeholder('Enter price')
                            ->disabled()
                            ->columnSpan(6),
                        TextInput::make('delivery_type')
                            ->label('Hisoblash turi')
                            ->disabled()
                            ->columnSpan(6),

                        TextInput::make('quantity')
                            ->label(trans("Quantity"))
                            ->numeric()
                            ->default(1)
                            ->placeholder('Enter quantity')
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('provider')
                            ->label(trans("Provider"))
                            ->placeholder('Provider name')
                            ->columnSpan(12),

                        // Properties
                    ])
                    ->columnSpan(8),
                Repeater::make('properties')
                    ->relationship()
                    ->label(trans("properties"))
                    ->schema([
                        $locale == 'zh_CN'
                        ? Textarea::make('originalName')
                            ->label(trans("name"))
                            ->columnSpan(6)
                        : Textarea::make('name')
                            ->label(trans("name"))
                            ->columnSpan(6),

                        $locale == 'zh_CN'
                        ? Textarea::make('originalValue')
                            ->label(trans("value"))
                            ->columnSpan(6)
                        : Textarea::make('value')
                            ->label(trans("value"))
                            ->columnSpan(6),
                    ])
                    ->columns(12)
                    ->columnSpan(12),
            ])
            ->columns([
                'md' => 12, // Make sure the total columns span adds up to 12 for consistency
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip('Open product')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(
                        fn(array $arguments, Repeater $component): ?string =>
                        $component->getRawItemState($arguments['item'])['product_url'] ?? null,
                        shouldOpenInNewTab: true
                    )
                    ->hidden(
                        fn(array $arguments, Repeater $component): bool =>
                        blank($component->getRawItemState($arguments['item'])['id'])
                    ),
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

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getLabel(): string
    {
        return trans('orders');
    }
}
