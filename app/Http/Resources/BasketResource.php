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
                    'id' => $property->id,
                    'name' => $property->name,
                    'value' => $property->value,
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
        ];
    }
}
