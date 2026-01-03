<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $properties = $this->properties->map(function ($property) {
            return [
                'id' => "$property->id",
                'name' => $property->name,
                'value' => $property->value,
                'originalName' => $property->originalName,
                'originalValue' => $property->originalValue
            ];
        });
        return [
            'id' => $this->id,
            'title' => $this['title'],
            'product_id' => $this['product_id'],
            'image_url' => $this['image_url'],
            'price' => $this['price'],
            'quantity' => $this['quantity'],
            'sku_id' => $this['skuId'],
            'provider' => $this['provider'],
            'product_url' => $this['product_url'],
            'seller_id' => $this['seller_id'],
            'properties' => $properties,
            'user_info' => [
                'name' => $this['user']['name'],
                'phone' => $this['user']['phone'],
            ],
            'user_location' => [
                'region' => $this['user']['region'],
                'district' => $this['user']['district'],
            ],
            'product_info' => $this['product_info'] ?? null,
            'quantityRanges' => $this['quantity_ranges'] ?? null,
            'maxQuantity' => $this['max_quantity'] ?? null,
            'original_price_yuan' => $this['original_price_yuan'] ?? 0,
            'local_delivery_yuan' => $this['local_delivery_yuan'] ?? 0,
            'profit_amount' => $this['profit_amount'] ?? 0,
        ];
    }
}
