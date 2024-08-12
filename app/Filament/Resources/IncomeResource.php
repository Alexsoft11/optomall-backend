<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Filament\Resources\IncomeResource\RelationManagers;
use App\Filament\Resources\IncomeResource\Widgets\TotalAmount;
use App\Models\Income;
use Illuminate\Support\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IncomeResource extends Resource
{
    protected static ?string $navigationGroup = "Buyurtmalar";

    protected static ?string $model = Income::class;

    protected static ?string $label = "reciepts";

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->label(trans("user_id"))
                    ->numeric(),
                Forms\Components\TextInput::make('order_id')
                    ->required()
                    ->label(trans("order_id"))
                    ->numeric(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->label(trans("amount"))
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->label(trans("type"))
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->label(trans("user_id"))
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->numeric()
                    ->label(trans("order_id"))
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                ->label(trans("amount"))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                ->label(trans("type"))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                ->label(trans("created_at"))
                    ->sortable()
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('date')
                            ->label('Select Date')
                    ])
                    ->query(function ($query, $data) {
                        if (!empty($data['date'])) {
                            return $query->whereDate('created_at', $data['date']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['date'])) {
                            return null;
                        }

                        return 'Created at ' . Carbon::parse($data['date'])->toFormattedDateString();
                    }),
                Filter::make('type')
                    ->form([
                        Select::make('type')->options(['order' => 'Buyurtma', 'cargo' => 'Kargo'])->label('Tipi')
                    ])
                    ->query(function ($query, $data) {
                        if (!empty($data['type'])) {
                            return $query->where('type', $data['type']);
                        }
                        return $query;
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
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
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TotalAmount::class,
        ];
    }


    public static function getNavigationLabel(): string
    {
        return trans('reciepts');
    }

    public static function getLabel(): string
    {
        return trans('reciepts');
    }
}
