<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiSearchHistoryResource\Pages;
use App\Models\AiSearchHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class AiSearchHistoryResource extends Resource
{
    protected static ?string $model = AiSearchHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'AI History';

    protected static ?string $navigationGroup = 'AI Management';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user.name')
                    ->label('User Name')
                    ->disabled(),
                Forms\Components\TextInput::make('user.last_name')
                    ->label('Last Name')
                    ->disabled(),
                Forms\Components\Textarea::make('prompt')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Textarea::make('generated_keywords')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\TextInput::make('items_provider')
                    ->disabled(),
                Forms\Components\TextInput::make('ai_model')
                    ->disabled(),
                Forms\Components\TextInput::make('total_tokens')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->width(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.last_name')
                    ->label('Lastname')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prompt')
                    ->limit(40)
                    ->searchable()
                    ->tooltip(fn(AiSearchHistory $record): string => $record->prompt),
                Tables\Columns\TextColumn::make('generated_keywords')
                    ->label('AI Response')
                    ->limit(40)
                    ->copyable()
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state),
                Tables\Columns\TextColumn::make('total_tokens')
                    ->label('Used Tokens')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No delete bulk action
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('User Information')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')->label('Name'),
                        Infolists\Components\TextEntry::make('user.last_name')->label('Lastname'),
                        Infolists\Components\TextEntry::make('user.email')->label('Email'),
                        Infolists\Components\TextEntry::make('user.phone')->label('Phone'),
                    ]),
                Infolists\Components\Section::make('Interaction Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('prompt')
                            ->columnSpanFull()
                            ->label('User Prompt'),
                        Infolists\Components\TextEntry::make('generated_keywords')
                            ->columnSpanFull()
                            ->label('AI Response (Keywords)')
                            ->copyable()
                            ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                            ->markdown(),
                    ]),
                Infolists\Components\Section::make('Technical Metadata')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('items_provider')->label('Provider'),
                        Infolists\Components\TextEntry::make('ai_model')->label('AI Model'),
                        Infolists\Components\TextEntry::make('created_at')->dateTime()->label('Time'),
                    ]),
                Infolists\Components\Section::make('Token Usage')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('input_tokens')->label('Input Tokens'),
                        Infolists\Components\TextEntry::make('output_tokens')->label('Output Tokens'),
                        Infolists\Components\TextEntry::make('total_tokens')->label('Total Tokens')
                            ->weight('bold')
                            ->color('warning'),
                    ]),
                Infolists\Components\Section::make('User Feedback')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\IconEntry::make('feedback_like')
                            ->boolean()
                            ->label('Liked?'),
                        Infolists\Components\TextEntry::make('feedback_comment')
                            ->label('Comment')
                            ->placeholder('No comment'),
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
