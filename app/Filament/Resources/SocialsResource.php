<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialsResource\Pages;
use App\Models\Socials;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class SocialsResource extends Resource
{
    protected static ?string $model = Socials::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('icon')
                    ->image()
                    ->required()
                    ->disk('public')
                    ->directory('icons')
                    ->maxSize(2048),

                TextInput::make('path')
                    ->required()
                    ->maxLength(255)
                    ->url(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                ImageColumn::make('icon')
                    ->disk('public')
                    ->searchable(),

                TextColumn::make('path')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('name')
                    ->label('Name')
                    ->query(fn (Builder $query, array $data): Builder => $query->where('name', 'like', "%{$data['name']}%"))
                    ->form([
                        TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Enter name')
                    ]),
            
                Filter::make('created_at')
                    ->label('Created Date')
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['from'])) {
                            $query->whereDate('created_at', '>=', $data['from']);
                        }
                        if (!empty($data['until'])) {
                            $query->whereDate('created_at', '<=', $data['until']);
                        }
                        return $query;
                    })
                    ->form([
                        DatePicker::make('from')
                            ->label('From')
                            ->placeholder('Select start date'),
            
                        DatePicker::make('until')
                            ->label('Until')
                            ->placeholder('Select end date'),
                    ]),
            ])
            
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocials::route('/'),
            'create' => Pages\CreateSocials::route('/create'),
            'view' => Pages\ViewSocials::route('/{record}'),
            'edit' => Pages\EditSocials::route('/{record}/edit'),
        ];
    }
}
