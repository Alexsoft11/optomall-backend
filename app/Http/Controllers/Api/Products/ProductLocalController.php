<?php

namespace App\Http\Controllers;

use App\Models\ProductLocal;
use App\Models\PrSku;
use App\Models\PrImage;
use Illuminate\Http\Request;

class ProductLocalController extends Controller
{
    public function index()
    {
        return ProductLocal::with(['skus', 'images', 'category'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'status' => 'required|string|in:active,inactive',
            'category_id' => 'required|exists:pr_categories,id',
            'brand' => 'nullable|string|max:255',
            'skus' => 'required|array',
            'images' => 'nullable|array',
        ]);

        $product = ProductLocal::create($validated);

        foreach ($validated['skus'] as $sku) {
            $product->skus()->create($sku);
        }

        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $url) {
                $product->images()->create(['url' => $url]);
            }
        }

        return response()->json($product->load(['skus', 'images', 'category']), 201);
    }

    public function show($id)
    {
        return ProductLocal::with(['skus', 'images', 'category'])->findOrFail($id);
    }
}
