<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Filament\Resources\IncomeResource\RelationManagers;
use App\Filament\Resources\IncomeResource\Widgets\IncomeStatsOverview;
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
                Forms\Components\TextInput::make('order_group_id')
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
                Tables\Columns\TextColumn::make('order_group_id')
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
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label('Date from'),
                        DatePicker::make('to')->label('Date to'),
                    ])
                    ->query(function ($query, $data) {
                        $from = $data['from'] ?? null;
                        $to = $data['to'] ?? null;

                        if ($from) {
                            $query->whereDate('created_at', '>=', $from);
                        }
                        if ($to) {
                            $query->whereDate('created_at', '<=', $to);
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['from'] ?? null;
                        $to = $data['to'] ?? null;

                        if (!$from && !$to) return null;

                        return "From {$from} to {$to}";
                    }),
                Filter::make('month')
                    ->form([
                        Select::make('month')
                            ->label('Select month')
                            ->options([
                                1 => 'Yanvar',
                                2 => 'Fevral',
                                3 => 'Mart',
                                4 => 'Aprel',
                                5 => 'May',
                                6 => 'Iyun',
                                7 => 'Iyul',
                                8 => 'Avgust',
                                9 => 'Sentabr',
                                10 => 'Oktabr',
                                11 => 'Noyabr',
                                12 => 'Dekabr',
                            ])
                            ->searchable(),
                    ])
                    ->query(function ($query, $data) {
                        if (!empty($data['month'])) {
                            return $query->whereMonth('created_at', $data['month']);
                        }
                        return $query;
                    })
                    ->indicateUsing(
                        fn($data) =>
                        !empty($data['month']) ? "Month: {$data['month']}" : null
                    ),

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
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderGroupRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomes::route('/'),
            'view'  => Pages\ViewIncome::route('/{record}'),
            // 'create' => Pages\CreateIncome::route('/create'),
            // 'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            IncomeStatsOverview::class,
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
