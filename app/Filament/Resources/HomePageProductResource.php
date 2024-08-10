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

class HomePageProductResource extends Resource
{
    protected static ?string $navigationGroup = "products";

    protected static ?string $navigationLabel = "Bosh sahifa uchun mahsulotlar";

    protected static ?string $label = "Bosh sahifa uchun mahsulotlar";

    protected static ?string $model = HomePageProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_uz')
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_ru')
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->maxLength(255),
                Forms\Components\TextInput::make('discounted_price')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')->disk('public'),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title_uz')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
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
            //
        ];
    }

    // public static function getNavigationLabel(): string
    // {
    //     return trans('products');
    // }

    public static function getNavigationGroup(): string
    {
        return trans('products');
    }
    public static function getLabel(): string
    {
        return trans('products');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomePageProducts::route('/'),
            'create' => Pages\CreateHomePageProduct::route('/create'),
            'edit' => Pages\EditHomePageProduct::route('/{record}/edit'),
        ];
    }
}
