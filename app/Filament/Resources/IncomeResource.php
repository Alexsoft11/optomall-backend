<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Filament\Resources\IncomeResource\RelationManagers;
use App\Filament\Resources\IncomeResource\Widgets\IncomeStatsOverview;
use App\Models\Income;
use App\Models\User;
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
                    ->label(trans("user_id"))
                    ->sortable()
                    ->searchable()
                    ->extraAttributes([
                        'class' => 'text-blue-600 cursor-pointer'
                    ]),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->formatStateUsing(function ($record) {
                        if (!$record->user) return '-';
                        return $record->user->name . ' ' . $record->user->last_name . ' (' . $record->user->unique_id . ')';
                    })
                    ->searchable([
                        'users.name',
                        'users.last_name',
                        'user_id',
                        'users.unique_id',
                    ]),


                Tables\Columns\TextColumn::make('orderGroup.unique_id')
                    ->label(trans("order_id"))
                    ->sortable()
                    ->extraAttributes([
                        'class' => 'text-blue-600 cursor-pointer'
                    ])
                    ->searchable(
                        [
                            'order_groups.unique_id',
                            'order_groups.id'
                        ]
                    ),

                Tables\Columns\TextColumn::make('amount')
                    ->label(trans("amount"))
                    ->searchable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->label('Summa'),
                        Tables\Columns\Summarizers\Count::make()->label('Soni'),
                    ]),


                Tables\Columns\TextColumn::make('type')
                    ->label(trans("type"))
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans("created_at"))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter::make('user')
                //     ->form([
                //         Select::make('user_id')
                //             ->label('User')
                //             ->searchable()
                //             ->options(
                //                 \App\Models\User::query()
                //                     ->selectRaw("id, CONCAT(name,' ',last_name,' (ID: ',id,')') as full_name")
                //                     ->pluck('full_name', 'id')
                //             )
                //             ->placeholder('Select user')
                //     ])
                //     ->query(function ($query, $data) {
                //         if (!empty($data['user_id'])) {
                //             return $query->where('user_id', $data['user_id']);
                //         }
                //         return $query;
                //     })
                //     ->indicateUsing(
                //         fn($data) =>
                //         !empty($data['user_id'])
                //             ? 'User: ' . \App\Models\User::find($data['user_id'])->full_name ?? ''
                //             : null
                //     ),

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
            ->actions([
                Tables\Actions\Action::make('open_order_group')
                    ->label('')
                    ->tooltip('Open Order Group')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn($record) => route('filament.admin.resources.order-groups.edit', $record->order_group_id))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('open_user')
                    ->label('')
                    ->tooltip('Open User')
                    ->icon('heroicon-o-user')
                    ->url(fn($record) => route('filament.admin.resources.users.edit', $record->user_id))
                    ->openUrlInNewTab(),
            ])
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
