<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderGroupResource\Pages;
use App\Filament\Resources\Shop\OrderGroupResource\RelationManagers\OrderGroupRelationManager;
use App\Models\OrderGroup;
use App\Models\OrderStatus;
use App\Models\User;
use Filament\Forms\Components\Fieldset;
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
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class OrderGroupResource extends Resource
{
    protected static ?string $navigationGroup = "Buyurtmalar";

    protected static ?string $label = "order_groups";

    protected static ?string $navigationLabel = "order_groups";

    protected static ?string $model = OrderGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('unique_id')->label('Order group id'),
                Fieldset::make('Foydalanuvchi')->relationship('user')->label(trans("users"))
                    ->schema([
                        TextInput::make('unique_id')->label('User Id'),
                        TextInput::make('name')->label(trans("ism")),
                        TextInput::make('last_name')->label(trans("last_name")),
                        TextInput::make('phone')->label(trans("phone")),
                        TextInput::make('email')->label(trans("email")),
                        // TextInput::make('adress')->label(trans("location")),
                        TextInput::make('region')->label(trans("region")),
                        TextInput::make('district')->label(trans("district")),
                    ])->disabled()->columnSpan(['sm' => 2]),
                Group::make()
                    ->schema([
                        Section::make(trans("checks"))
                            ->schema([
                                Repeater::make('checks')->label(trans("checks"))->relationship('checks')->schema([
                                    FileUpload::make('image')->disk('public')->label(trans("check"))->openable()->downloadable()->previewable(true),
                                ]),
                            ]),
                    ])
                    ->disabled(),
                Select::make('status')
                    ->label('Status')
                    ->options(
                        OrderStatus::pluck('name_uz', 'id')->mapWithKeys(function ($item, $key) {
                            return [(string) $key => (string) $item];
                        })
                    ),
                TextInput::make('total_price')->label(trans("total_amount"))->disabled(),
                Toggle::make('is_paid'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.unique_id')->label(trans("user_id"))->sortable(),
                TextColumn::make('user.name')->label(trans("ism"))->sortable(),
                TextColumn::make('total_price')->label(trans("price"))->sortable(),
                TextColumn::make('getStatus.name_uz')
                    ->label("Status")
                    ->sortable()
                    ->searchable(),
                ColorColumn::make('getStatus.color')->label(trans("status_color")),
                TextColumn::make('created_at')->label(trans("created_at"))->sortable(),
            ])
            ->filters([
                Filter::make('Basket Unique ID')
                    ->form([
                        TextInput::make('basket_unique_id')
                            ->label('Search by Order Unique ID'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['basket_unique_id']) {
                            $query->whereHas('orders', function (Builder $query) use ($data) {
                                $query->where('unique_id', 'like', '%' . $data['basket_unique_id'] . '%');
                            });
                        }
                    }),
                Filter::make('User Unique ID')
                    ->form([
                        TextInput::make('user_unique_id')
                            ->label('Search by User Unique ID'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['user_unique_id']) {
                            $query->whereHas('user', function (Builder $query) use ($data) {
                                $query->where('unique_id', 'like', '%' . $data['user_unique_id'] . '%');
                            });
                        }
                    }),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            OrderGroupRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderGroups::route('/'),
            'create' => Pages\CreateOrderGroup::route('/create'),
            'edit' => Pages\EditOrderGroup::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return trans('order_groups');
    }

    public static function getLabel(): string
    {
        return trans('order_groups');
    }
}
