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

        foreach ($this['basketItems'] as $item) {
            return [
                'id' => $this->id,
                'title' => $item['title'],
                'product_id' => $item['product_id'],
                'image_url' => $item['image_url'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'size' => $item['size'],
                'color' => $item['color'],
                'sku_id' => $item['skuId'],
                'seller_id' => $item['seller_id'],
            ];
        }
    }
}
