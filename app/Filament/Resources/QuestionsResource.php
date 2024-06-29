<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionsResource\Pages;
use App\Filament\Resources\QuestionsResource\RelationManagers;
use App\Models\Questions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionsResource extends Resource
{

    protected static ?string $navigationGroup = "Savol-javoblar";

    protected static ?string $navigationLabel = "Savollar";

    protected static ?string $model = Questions::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Tables\Columns\TextColumn::make('name_uz'),
                Tables\Columns\TextColumn::make('name_ru'),
                Tables\Columns\TextColumn::make('name_en'),
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestions::route('/create'),
            'edit' => Pages\EditQuestions::route('/{record}/edit'),
        ];
    }
}
