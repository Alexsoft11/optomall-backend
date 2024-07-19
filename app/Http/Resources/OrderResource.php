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
            'status' => $this->status,
            'status_color' => $this->status_color,
            'total_price' => $this->total_price,
            'user_name' => $this->user_name,
            'user_phone' => $this->user_phone,
            'country' => $this->country == 'uzbekistan' ? url('/flags/uzbekistan.png') : url('/flags/china.png'),
            'location_url' => $this->location_url,
            'location_name' => $this->location_name,
            'substatus' => [
                'uz' => $this->substatus_uz,
                'ru' => $this->substatus_ru,
                'en' => $this->substatus_en,
            ],
            'substatus_color' => $this->substatus_color,
            'kg' => $this->kg,
            'created_at' => $this->created_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
