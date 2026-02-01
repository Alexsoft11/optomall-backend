<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiSearchHistoryResource\Pages;
use App\Models\AiSearchHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AiSearchHistoryResource extends Resource
{
    protected static ?string $model = AiSearchHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'AI History';

    protected static ?string $navigationGroup = 'AI Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->disabled(),
                Forms\Components\TextInput::make('user_id')
                    ->numeric()
                    ->label('User ID'),
                Forms\Components\Textarea::make('prompt')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('generated_keywords')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('items_provider'),
                Forms\Components\TextInput::make('ai_model'),
                Forms\Components\Toggle::make('feedback_like')
                    ->label('Liked'),
                Forms\Components\Textarea::make('feedback_comment')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->sortable()->label('User'),
                Tables\Columns\TextColumn::make('prompt')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('items_provider')->sortable(),
                Tables\Columns\TextColumn::make('ai_model')->sortable(),
                Tables\Columns\IconColumn::make('feedback_like')
                    ->boolean()
                    ->label('Like'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListAiSearchHistories::route('/'),
        ];
    }
}
