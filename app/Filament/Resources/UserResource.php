<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\RelationManagers\OrderRelationManager;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $label = "users";

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->label(trans('roles'))
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->label(trans('ism'))
                    ->maxLength(100),
                Forms\Components\TextInput::make('last_name')
                    ->label(trans('last_name'))
                    ->maxLength(100),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->label(trans('phone'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->label(trans('email'))
                    ->maxLength(255),
                // Forms\Components\TextInput::make('region')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('district')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('street')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('house')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('post_index')
                //     ->maxLength(255),
                Forms\Components\TextInput::make('adress')
                    ->label(trans('location'))
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('is_register')
                    ->label(trans('auth_method'))
                    ->formatStateUsing(fn(bool $state): string => $state
                        ? '📧 ' . trans('email')
                        : '📱 ' . trans('phone')),
                Tables\Columns\TextColumn::make('unique_id')
                    ->numeric()
                    ->sortable()
                    ->label(trans('unique_id')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label(trans('ism')),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->label(trans('last_name')),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->label(trans('phone')),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label(trans('email')),
                Tables\Columns\TextColumn::make('adress')
                    ->searchable()
                    ->label(trans('location')),
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
            OrderRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return trans('users');
    }

    public static function getLabel(): string
    {
        return trans('users');
    }
}
