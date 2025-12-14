<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BasketItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'basketItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('image_url')
                            ->label('Image URL')
                            ->columnSpanFull(),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->prefix('¥'),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric(),
                                Forms\Components\TextInput::make('max_quantity')
                                    ->numeric(),
                            ])->columns(3),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('provider'),
                                Forms\Components\TextInput::make('product_id'),
                                Forms\Components\TextInput::make('skuId'),
                            ])->columns(3),

                        Forms\Components\TextInput::make('product_url')
                            ->label('Product URL')
                            ->url()
                            ->columnSpanFull(),
                            
                        Forms\Components\KeyValue::make('product_info')
                            ->label('Product Info')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->money('uzs'),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('provider')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_id')
                    ->label('Product ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('product_url')
                    ->label('Product URL')
                    ->url(fn ($record) => $record->product_url)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
