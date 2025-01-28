<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductLocal;
use App\Models\PrSku;
use App\Models\PrImage;
use App\Models\Skus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductLocalController extends Controller
{
    public function index()
    {
        return ProductLocal::with(['skus', 'images', 'category'])->get();
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'status' => 'required|string|in:active,inactive',
            'category_id' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'brand' => 'nullable|string|max:255',
            'skuProps' => 'required|array',
            'skuProps.*.prop_name' => 'required|string',
            'skuProps.*.values' => 'required|array',
            'skuProps.*.values.*.name' => 'required|string',
            'skuProps.*.values.*.imageUrl' => 'nullable|url',
            'images' => 'required|array',
            'images.*.image_url' => 'required|string',
            'images.*.is_main' => 'nullable|boolean',
        ]);

        $product = ProductLocal::create($validated);

        $skusProps =   $this->createSkuProps($product, $validated['skuProps']);
        $this->generateSkus($skusProps, $product);

        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $image) {
                $product->images()->create(['image_url' => $image['image_url'], 'is_main' => $image['is_main'] ?? false]);
            }
        }
        DB::commit();
        $result = $product->load(['sku_props.values', 'images', 'category', 'skus']);
        return response()->json($result, 201);
    }

    protected function createSkuProps($product, $skuProps)
    {
        $createdData = []; // Initialize an array to store the created data

        foreach ($skuProps as $prop) {
            $skuProp = $product->sku_props()->create([
                'prop_name' => $prop['prop_name'],
            ]);

            // Collect the created `skuProp` data
            $createdPropData = $skuProp->toArray();

            $createdPropData['values'] = []; // Initialize an array for the values

            foreach ($prop['values'] as $value) {
                $createdValue = $skuProp->values()->create($value);

                // Collect the created value data
                $createdPropData['values'][] = $createdValue->toArray();
            }

            // Add the prop with its values to the created data array
            $createdData[] = $createdPropData;
        }

        return $createdData; // Return all the created data
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
                'sku_id' => (string) mt_rand(1000000000000, 9999999999999), // Auto-generate a unique SKU ID
                'specid' => Str::uuid()->toString(), // Generate a unique specId
                'price' => $product->price, // Default sale price
                'total_price' => $product->price, // Default sale price
                'origin_price' => $product->price, // Default origin price
                'quantity' => $product->quantity, // Default quantity
                'properties' => implode(';', $properties), // Format properties (e.g., "0:0;1:6")
                'properties_name' => implode(';', $properties_name), // Format properties_name (e.g., "颜色:3L-97;尺码:4XL")
                'sale_count' => 0, // Default sale count
                'package_info' => null, // Default package info
                'product_local_id' => $product->id,
            ];
            $skus[] = Skus::create($sku);
        }

        return $skus;
    }

    private function generateCombinations(array $skuProps): array
    {
        $combinations = [[]];

        foreach ($skuProps as $skuProp) {
            $newCombinations = [];

            foreach ($combinations as $combination) {
                foreach ($skuProp['values'] as $value) {
                    $newCombinations[] = array_merge($combination, [
                        [
                            'pid' => $skuProp['id'],
                            'prop_name' => $skuProp['prop_name'],
                            'vid' => $value['id'],
                            'value_name' => $value['name'],
                        ],
                    ]);
                }
            }

            $combinations = $newCombinations;
        }

        return $combinations;
    }



    public function show($id)
    {
        return ProductLocal::with(['skus', 'images', 'category'])->findOrFail($id);
    }
}
