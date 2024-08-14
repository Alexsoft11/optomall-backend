<?php

namespace App\Filament\Resources\Shop\OrderResource\RelationManagers;

use Akaunting\Money\Currency;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderSubStatus;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;

class OrderGroupRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $recordTitleAttribute = 'reference';

    public function form(Form $form): Form
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
                                ->options(OrderStatus::all()->pluck('name_uz', 'id'))
                                ->reactive(),
                            Select::make('substatus')
                                ->label('Sub Status')
                                ->options(OrderSubStatus::all()->pluck('name_uz', 'id'))
                                ->reactive(),
                        ])
                    ])
            ]);
    }

    public function table(Table $table): Table
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
                // Add your filters here
            ])
            ->actions([
                Tables\Actions\Action::make('openOrder')
                    ->tooltip('Open order')
                    ->label(trans("open"))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn($record) => route('filament.admin.resources.orders.edit', $record->id), shouldOpenInNewTab: true)
                    ->hidden(fn($record) => !$record->id),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
