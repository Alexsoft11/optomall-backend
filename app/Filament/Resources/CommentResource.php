<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
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
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Textarea;


class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationGroup = "products";

    protected static ?string $navigationLabel = "comments";

    protected static ?string $label = "comments";


    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Fieldset::make('User')
                    ->relationship('user')
                    ->label(trans("users"))
                    ->schema([
                        TextInput::make('name')
                            ->label(trans("ism")),
                        TextInput::make('phone')
                        ->label(trans("phone")),
                    ]),
                TextInput::make('product_id')
                    ->label(trans("name"))
                    ->disabled()
                    ->columnSpan('full'),

                Textarea::make('name')
                    ->label(trans('comment'))
                    ->required()
                    ->columnSpan('full')
                    ->rows(5),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(trans("ism")),
                TextColumn::make('product.name')
                    ->label(trans("name"))
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label(trans("created_at"))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label(trans("updated_at"))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('name')
                    ->label(trans("ism"))
                    ->query(fn(Builder $query, array $data): Builder => $query->where('name', 'like', "%{$data['name']}%"))
                    ->form([
                        TextInput::make('name')
                            ->label(trans("ism"))
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
            'index' => Pages\ListComment::route('/'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
            'view' => Pages\ViewComment::route('/{record}/view'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'product']);  // Ensure relationships are eager loaded
    }

    public static function getNavigationLabel(): string
    {
        return trans('comments');
    }
    public static function getLabel(): string
    {
        return trans('comments');
    }

    public static function getNavigationGroup(): string
    {
        return trans('products');
    }
}
