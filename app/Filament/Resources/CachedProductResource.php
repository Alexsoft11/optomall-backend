<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CachedProductResource\Pages;
use App\Models\CachedProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CachedProductResource extends Resource
{
    protected static ?string $model = CachedProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'id',
                'external_id',
                'title',
                'provider',
                'language',
                'image',
                'price',
                'views',
                'is_available',
                'synced_at',
                'created_at',
            ])
            ->orderBy('synced_at', 'desc');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('products');
    }

    public static function getNavigationLabel(): string
    {
        return trans('cached_products');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(trans('product_information'))
                    ->schema([
                        Forms\Components\TextInput::make('external_id')
                            ->label(trans('external_id'))
                            ->disabled(),
                        Forms\Components\TextInput::make('provider')
                            ->label(trans('provider'))
                            ->disabled(),
                        Forms\Components\TextInput::make('language')
                            ->label(trans('language'))
                            ->disabled(),
                        Forms\Components\TextInput::make('title')
                            ->label(trans('name'))
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('price')
                            ->label(trans('price'))
                            ->prefix('¥')
                            ->disabled(),
                        Forms\Components\TextInput::make('sales')
                            ->label(trans('total_sales'))
                            ->disabled(),
                        Forms\Components\TextInput::make('views')
                            ->label(trans('total_views'))
                            ->disabled(),
                        Forms\Components\TextInput::make('rating')
                            ->label(trans('rating'))
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make(trans('cache_status'))
                    ->schema([
                        Forms\Components\Toggle::make('is_available')
                            ->label(trans('available_from_provider'))
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('synced_at')
                            ->label(trans('last_synced'))
                            ->disabled(),
                        Forms\Components\Placeholder::make('cache_status')
                            ->label(trans('cache_status'))
                            ->content(fn (CachedProduct $record): string => $record->getCacheStatus()),
                        Forms\Components\Placeholder::make('hours_until_expiry')
                            ->label(trans('hours_until_expiry'))
                            ->content(fn (CachedProduct $record): string => round($record->getHoursUntilExpiry(), 1) . ' hours'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(trans('images'))
                    ->schema([
                        Forms\Components\TextInput::make('image')
                            ->label(trans('image_url'))
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(trans('image'))
                    ->circular()
                    ->size(50)
                    ->getStateUsing(fn (CachedProduct $record): string => "https://china-proxy.optomall.uz/image-proxy?url=" . urlencode($record->image)),
                Tables\Columns\TextColumn::make('external_id')
                    ->label(trans('external_id'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(trans('name'))
                    ->limit(40)
                    ->searchable()
                    ->tooltip(fn (CachedProduct $record): string => $record->title ?? ''),
                Tables\Columns\TextColumn::make('provider')
                    ->label(trans('provider'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1688' => 'success',
                        'taobao' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('language')
                    ->label(trans('language'))
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('price')
                    ->label(trans('price'))
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('views')
                    ->label(trans('total_views'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->label(trans('available'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('synced_at')
                    ->label(trans('last_synced'))
                    ->dateTime()
                    ->sortable()
                    ->description(fn (CachedProduct $record): string => $record->isExpired() ? trans('expired') : trans('fresh')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')
                    ->label(trans('provider'))
                    ->options([
                        '1688' => '1688',
                        'taobao' => 'Taobao',
                    ]),
                Tables\Filters\SelectFilter::make('language')
                    ->label(trans('language'))
                    ->options([
                        'en' => 'English',
                        'ru' => 'Russian',
                        'uz' => 'Uzbek',
                    ]),
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label(trans('availability')),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->expired())
                    ->label(trans('expired_cache')),
                Tables\Filters\Filter::make('fresh')
                    ->query(fn (Builder $query): Builder => $query->valid())
                    ->label(trans('fresh_cache')),
            ])
            ->actions([
                Tables\Actions\Action::make('open_product')
                    ->label(trans('open'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (CachedProduct $record): string => "https://optomall.uz/{$record->language}/product/{$record->external_id}?provider={$record->provider}")
                    ->openUrlInNewTab()
                    ->color('primary'),
                Tables\Actions\Action::make('similar_products')
                    ->label(trans('similar'))
                    ->icon('heroicon-o-magnifying-glass')
                    ->url(fn (CachedProduct $record): string => "https://optomall.uz/{$record->language}/search?imageUrl=" . urlencode($record->image) . "&provider={$record->provider}")
                    ->openUrlInNewTab()
                    ->color('gray'),
                Tables\Actions\ViewAction::make()
                    ->label(trans('details')),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (CachedProduct $record): string => "https://optomall.uz/{$record->language}/product/{$record->external_id}?provider={$record->provider}")
            ->openRecordUrlInNewTab()
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCachedProducts::route('/'),
            'view' => Pages\ViewCachedProduct::route('/{record}'),
        ];
    }
}
