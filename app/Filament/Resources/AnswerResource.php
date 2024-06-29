<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnswerResource\Pages;
use App\Filament\Resources\AnswerResource\RelationManagers;
use App\Models\Answer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

class AnswerResource extends Resource
{

    protected static ?string $navigationGroup = "Savol-javoblar";

    protected static ?string $navigationLabel = "Javoblar";

    protected static ?string $model = Answer::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('question_id')
                ->label(__('Question'))
                ->relationship('question', 'name_uz'),
            Forms\Components\TextInput::make('name_uz')
                ->label(__('Name (Uzbek)'))
                ->required(),
            Forms\Components\TextInput::make('name_ru')
                ->label(__('Name (Russian)'))
                ->required(),
            Forms\Components\TextInput::make('name_en')
                ->label(__('Name (English)'))
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('question.name_en')
                ->label(__('Question (English)')),
            TextColumn::make('name_uz')
                ->label(__('Name (Uzbek)')),
            TextColumn::make('name_ru')
                ->label(__('Name (Russian)')),
            TextColumn::make('name_en')
                ->label(__('Name (English)')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListAnswer::route('/'),
            'create' => Pages\CreateAnswer::route('/create'),
            'edit' => Pages\EditAnswer::route('/{record}/edit'),
        ];
    }
}
