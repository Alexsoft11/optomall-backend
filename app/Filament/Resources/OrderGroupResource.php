<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderGroupResource\Pages;
use App\Filament\Resources\OrderGroupResource\RelationManagers;
use App\Models\OrderGroup;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
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
                Group::make()
                    ->schema([
                        Section::make('Buyurtma checklari')
                            ->schema([
                                Repeater::make('checks')->label('Cheklar')->relationship('checks')->schema([
                                    FileUpload::make('image')->disk('public')->label('Chek')->openable()->downloadable(),
                                ]),
                            ]),
                    ])
                    ->disabled(),
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
            //
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
