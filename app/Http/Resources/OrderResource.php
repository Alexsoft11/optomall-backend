<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $items = $this->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'order_id' => $this->id,
                'title' => $item->title,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'color' => $item->color,
                'size' => $item->size,
                'provider' => $item->provider,
                'image_url' => url('storage/' . $item->image_url),
            ];
        });

        return [
            'unique_id' => $this->unique_id,
            'id' => $this->id,
            'status' => [
                'uz' => $this->getStatus->name_uz ?? null,
                'ru' => $this->getStatus->name_ru ?? null,
                'en' => $this->getStatus->name_en ?? null,
            ],
            'status_color' => $this->getStatus->color ?? null,
            'isPaid' => $this->is_paid == 0 ? false : true,
            'total_price' => $this->total_price,
            'user_name' => $this->user_name,
            'user_phone' => $this->user_phone,
            'country' => $this->country === 'uzbekistan' ? url('/flags/uzbekistan.png') : url('/flags/china.jpg'),
            'location_region' => $this->region,
            'location_district' => $this->district,
            'kg' => $this->kg,
            'cargo_price' => $this->kg * ($request->kgPrice ?? 0),
            'created_at' => $this->created_at,
            'items' => $items,
        ];
    }
}
