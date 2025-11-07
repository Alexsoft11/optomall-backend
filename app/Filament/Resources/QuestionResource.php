<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionResource extends Resource
{

    protected static ?string $navigationGroup = "quest_answers";

    protected static ?string $navigationLabel = "questions";

    protected static ?string $model = Question::class;

    protected static ?string $label = "questions";


    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_uz')
                ->label(trans("title_uz"))
                ->required(),
            Forms\Components\TextInput::make('name_ru')
                ->label(trans("title_ru"))
                ->required(),
            Forms\Components\TextInput::make('name_en')
                ->label(trans("title_en"))
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_uz')->label(trans("title_uz")),
                Tables\Columns\TextColumn::make('name_ru')->label(trans("title_ru")),
                Tables\Columns\TextColumn::make('name_en')->label(trans("title_en")),
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
            'index' => Pages\ListQuestion::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return trans('questions');
    }
    public static function getLabel(): string
    {
        return trans('questions');
    }

    public static function getNavigationGroup(): string
    {
        return trans('quest_answers');
    }
}
