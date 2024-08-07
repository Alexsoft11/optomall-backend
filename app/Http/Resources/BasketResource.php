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
            'id' => $this['id'],
            'title' => $this['title'],
            'image_url' => $this['image_url'],
            'price' => $this['price'],
            'quantity' => $this['quantity'],
            'size' => $this['size'],
            'color' => $this['color'],
        ];
    }
}
