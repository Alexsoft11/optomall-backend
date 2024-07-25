<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => [
                'uz' => $this->getStatus->name_uz ?? null,
                'ru' => $this->getStatus->name_ru ?? null,
                'en' => $this->getStatus->name_en ?? null
            ],
            'status_color' => $this->getStatus->color ?? null,
            'substatus' => [
                'uz' => $this->getSubStatus->name_uz ?? null,
                'ru' => $this->getSubStatus->name_ru ?? null,
                'en' => $this->getSubStatus->name_en ?? null,
            ],
            'substatus_color' => $this->getSubStatus->color ?? null,
            'substatus_slug' => $this->substatus_slug ?? null,
            'total_price' => $this->total_price,
            'user_name' => $this->user_name,
            'user_phone' => $this->user_phone,
            'country' => $this->country == 'uzbekistan' ? url('/flags/uzbekistan.png') : url('/flags/china.jpg'),
            'location_url' => $this->location_url,
            'location_name' => $this->location_name,
            'kg' => $this->kg,
            'created_at' => $this->created_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
