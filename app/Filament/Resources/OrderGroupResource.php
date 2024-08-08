<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderGroupResource\Pages;
use App\Filament\Resources\OrderGroupResource\RelationManagers;
use App\Filament\Resources\Shop\OrderResource\RelationManagers\OrderGroupRelationManager;
use App\Models\OrderGroup;
use App\Models\OrderStatus;
use Filament\Forms;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderGroupResource extends Resource
{
    protected static ?string $model = OrderGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('total_price')->label('Jami narxi')->disabled(),
                Fieldset::make('Foydalanuvchi')->relationship('user')
                    ->schema([
                        TextInput::make('last_name')->label('Familiyasi'),
                        TextInput::make('phone')->label('Telefon raqami'),
                        TextInput::make('email')->label('Pochta'),
                        TextInput::make('location')->label('Manzil linki'),
                        TextInput::make('region')->label('Viloyat'),
                        TextInput::make('district')->label('Tuman'),
                    ])->disabled()->columnSpan(['sm' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Buyurtma checklari')
                            ->schema([
                                Repeater::make('checks')->label('Cheklar')->relationship('checks')->schema([
                                    FileUpload::make('image')->disk('public')->label('Chek')->openable()->downloadable()->previewable(true),
                                ]),
                            ]),
                    ])
                    ->disabled(),
                Select::make('status')
                    ->label('Status')
                    ->options(OrderStatus::whereNotNull('name_uz')->pluck('name_uz', 'id'))
                    ->reactive(),
                Toggle::make('is_paid'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unique_id'),
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
}
