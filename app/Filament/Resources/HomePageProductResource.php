<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomePageProductResource\Pages;
use App\Models\HomePageProduct;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;

class HomePageProductResource extends Resource
{
    protected static ?string $navigationGroup = "products";
    protected static ?string $navigationLabel = "Bosh sahifa uchun mahsulotlar";
    protected static ?string $label = "home_page";
    protected static ?string $model = HomePageProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_uz')->maxLength(255),
                Forms\Components\TextInput::make('title_en')->maxLength(255),
                Forms\Components\TextInput::make('title_ru')->maxLength(255),
                Forms\Components\TextInput::make('price')->maxLength(255),
                Forms\Components\TextInput::make('discounted_price')->maxLength(255),
                Forms\Components\View::make('image')
                    ->label('Image Preview')
                    ->view('filament.components.image'),
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('product_url')
                    ->url()
                    ->maxLength(255),

                Select::make('provider')->options([
                    '1688' => '1688',
                    'taobao' => 'TaoBao',
                ])->required(),

                Select::make('type')->options([
                    'discount' => 'Discount',
                    'top' => 'Top products',
                    'recomend' => 'Recommend',
                ])->required(),

                Forms\Components\Toggle::make('is_deleted')
                    ->label('Deleted')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title_uz')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image'),
                IconColumn::make('is_deleted')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Deleted')
                    ->trueLabel('Only Deleted')
                    ->falseLabel('Only Active')
                    ->placeholder('All'),
                    
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (HomePageProduct $record): string => "https://optomall.uz/uz/product/{$record->product_id}?provider={$record->provider}")
            ->openRecordUrlInNewTab();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationGroup(): string
    {
        return 'Mahsulotlar';
    }
    public static function getLabel(): string
    {
        return 'Mahsulotlar';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomePageProducts::route('/'),
            'create' => Pages\CreateHomePageProduct::route('/create'),
            'edit' => Pages\EditHomePageProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Bosh sahifa mahsulotlari';
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
