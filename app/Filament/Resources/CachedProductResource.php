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

    protected static ?string $navigationGroup = 'Mahsulotlar';

    protected static ?string $navigationLabel = 'Keshdagi mahsulotlar';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\TextInput::make('external_id')
                            ->label('External ID')
                            ->disabled(),
                        Forms\Components\TextInput::make('provider')
                            ->disabled(),
                        Forms\Components\TextInput::make('language')
                            ->disabled(),
                        Forms\Components\TextInput::make('title')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('price')
                            ->prefix('¥')
                            ->disabled(),
                        Forms\Components\TextInput::make('sales')
                            ->label('Total Sales')
                            ->disabled(),
                        Forms\Components\TextInput::make('views')
                            ->label('Total Views')
                            ->disabled(),
                        Forms\Components\TextInput::make('rating')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Cache Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_available')
                            ->label('Available from Provider')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('synced_at')
                            ->label('Last Synced')
                            ->disabled(),
                        Forms\Components\Placeholder::make('cache_status')
                            ->label('Cache Status')
                            ->content(fn (CachedProduct $record): string => $record->getCacheStatus()),
                        Forms\Components\Placeholder::make('hours_until_expiry')
                            ->label('Hours Until Expiry')
                            ->content(fn (CachedProduct $record): string => round($record->getHoursUntilExpiry(), 1) . ' hours'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\TextInput::make('image')
                            ->label('Main Image URL')
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
                    ->label('Image')
                    ->circular()
                    ->size(50)
                    ->getStateUsing(fn (CachedProduct $record): string => "https://china-proxy.optomall.uz/image-proxy?url=" . urlencode($record->image)),
                Tables\Columns\TextColumn::make('external_id')
                    ->label('External ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->limit(40)
                    ->searchable()
                    ->tooltip(fn (CachedProduct $record): string => $record->title ?? ''),
                Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1688' => 'success',
                        'taobao' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('language')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('price')
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean(),
                Tables\Columns\TextColumn::make('synced_at')
                    ->label('Last Synced')
                    ->dateTime()
                    ->sortable()
                    ->description(fn (CachedProduct $record): string => $record->isExpired() ? 'Expired' : 'Fresh'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        '1688' => '1688',
                        'taobao' => 'Taobao',
                    ]),
                Tables\Filters\SelectFilter::make('language')
                    ->options([
                        'en' => 'English',
                        'ru' => 'Russian',
                        'uz' => 'Uzbek',
                    ]),
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Availability'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->expired())
                    ->label('Expired Cache'),
                Tables\Filters\Filter::make('fresh')
                    ->query(fn (Builder $query): Builder => $query->valid())
                    ->label('Fresh Cache'),
                Tables\Filters\Filter::make('most_viewed')
                    ->query(fn (Builder $query): Builder => $query->reorder()->orderBy('views', 'desc'))
                    ->label('Most Viewed')
                    ->toggle(), 
            ])
            ->actions([
                Tables\Actions\Action::make('open_product')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (CachedProduct $record): string => "https://optomall.uz/{$record->language}/product/{$record->external_id}?provider={$record->provider}")
                    ->openUrlInNewTab()
                    ->color('primary'),
                Tables\Actions\Action::make('similar_products')
                    ->label('Similar')
                    ->icon('heroicon-o-magnifying-glass')
                    ->url(fn (CachedProduct $record): string => "https://optomall.uz/{$record->language}/search?imageUrl=" . urlencode($record->image) . "&provider={$record->provider}")
                    ->openUrlInNewTab()
                    ->color('gray'),
                Tables\Actions\ViewAction::make()
                    ->label('Details'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('synced_at', 'desc')
            ->recordUrl(fn (CachedProduct $record): string => "https://optomall.uz/{$record->language}/product/{$record->external_id}?provider={$record->provider}")
            ->openRecordUrlInNewTab();
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
