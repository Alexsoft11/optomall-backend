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
        return [
            'id' => $this->id,
            'title' => $this['title'],
            'product_id' => $this['product_id'],
            'image_url' => $this['image_url'],
            'price' => $this['price'],
            'quantity' => $this['quantity'],
            'size' => $this['size'],
            'color' => $this['color'],
            'sku_id' => $this['skuId'],
            'provider' => $this['provider'],
            'product_url' => $this['product_url'],
            'seller_id' => $this['seller_id'],
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
