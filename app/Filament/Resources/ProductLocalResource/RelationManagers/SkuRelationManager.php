<?php

namespace App\Filament\Resources\ProductLocalResource\RelationManagers;

use App\Models\ProductLocal;
use App\Models\SkuProp;
use App\Models\Skus;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SkuRelationManager extends RelationManager
{
    protected static string $relationship = 'skus';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('sku_id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('quantity')
                    ->sortable(),
                TextColumn::make('price')
                    ->sortable(),
                TextColumn::make('sale_count')
                    ->sortable(),
                TextColumn::make('properties_name')
                    ->label('Properties')
                    ->wrap(),
            ])
            ->headerActions([
                Action::make('CreateSku')
                    ->label('Create SKU')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm SKU Creation')
                    ->modalDescription('Are you sure you want to create a new SKU?')
                    ->modalSubmitActionLabel('Yes, Create')
                    ->action(fn() => $this->handleCreateSku()),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('sku_id')
                ->disabled()
                ->label('SKU ID'),
            TextInput::make('quantity')
                ->numeric()
                ->required()
                ->minValue(0)
                ->label('Quantity'),
            TextInput::make('price')
                ->numeric()
                ->required()
                ->minValue(0)
                ->label('Price'),
            TextInput::make('origin_price')
                ->numeric()
                ->required()
                ->minValue(0)
                ->label('Original price'),
            TextInput::make('total_price')
                ->numeric()
                ->required()
                ->minValue(0)
                ->label('Current price'),
            TextInput::make('sale_count')
                ->numeric()
                ->minValue(0)
                ->label('Sales Count'),
            Textarea::make('properties_name')
                ->disabled()
                ->label('Properties'),
            Textarea::make('properties')
                ->disabled()
                ->label('Property id'),
        ]);
    }

    protected function handleCreateSku()
    {
        Log::info('Handling SKU generation.');

        $productId = $this->ownerRecord->id;

        // Delete existing SKUs
        Skus::where('product_local_id', $productId)->delete();
        Log::info("Deleted existing SKUs for product ID: {$productId}");

        // Fetch SKU properties and their values
        $skuProps = SkuProp::with('sku_values')
            ->where('product_local_id', $productId)
            ->get()
            ->map(function ($prop) {
                return [
                    'pid' => $prop->id,
                    'prop_name' => $prop->prop_name,
                    'values' => $prop->sku_values->map(function ($value) {
                        return [
                            'vid' => $value->id,
                            'value_name' => $value->name,
                        ];
                    })->toArray(),
                ];
            })
            ->toArray();

        // Check if properties are available
        if (empty($skuProps)) {
            Log::warning("No SKU properties found for product ID: {$productId}");
            return;
        }

        $product = ProductLocal::findOrFail($productId);

        // Generate and save SKUs
        $skus = $this->generateSkus($skuProps, $product);
        Log::info('Generated SKUs:', ['skus' => $skus]);
    }

    private function generateSkus(array $skuProps, ProductLocal $product): array
    {
        $skus = [];

        // Generate all possible combinations of skuValues from skuProps
        $combinations = $this->generateCombinations($skuProps);

        foreach ($combinations as $combination) {
            // Extract properties and properties_name
            $properties = [];
            $properties_name = [];

            foreach ($combination as $skuProp) {
                $properties[] = "{$skuProp['pid']}:{$skuProp['vid']}";
                $properties_name[] = "{$skuProp['prop_name']}:{$skuProp['value_name']}";
            }

            $sku = [
                'sku_id' => (string) mt_rand(1000000000000, 9999999999999),
                'specid' => Str::uuid()->toString(),
                'price' => $product->price,
                'total_price' => $product->price,
                'origin_price' => $product->price,
                'quantity' => $product->quantity,
                'properties' => implode(';', $properties),
                'properties_name' => implode(';', $properties_name),
                'sale_count' => 0,
                'package_info' => null,
                'product_local_id' => $product->id,
            ];
            $skus[] = Skus::create($sku);
        }

        return $skus;
    }

    private function generateCombinations(array $skuProps): array
    {
        if (empty($skuProps)) {
            return [];
        }

        $combinations = [[]];

        foreach ($skuProps as $prop) {
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach ($prop['values'] as $value) {
                    $newCombinations[] = array_merge($combination, [
                        [
                            'pid' => $prop['pid'],
                            'prop_name' => $prop['prop_name'],
                            'vid' => $value['vid'],
                            'value_name' => $value['value_name'],
                        ],
                    ]);
                }
            }
            $combinations = $newCombinations;
        }

        return $combinations;
    }
}
