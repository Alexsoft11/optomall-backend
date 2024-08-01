<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $amount = Exchanges::where('currency', 'usd')->first()->amount ?? 12000;

        return [
            'id' => $this->resource['num_iid'],
            'title' => $this->resource['title'],
            'price' => round($this->resource['orginal_price'] * $amount),
            'image' => $this->resource['pic_url'],
            'discounted_price' => round($this->resource['promotion_price'] * $amount),
            'discount_percent' => round((($this->resource['orginal_price'] - $this->resource['promotion_price']) / $this->resource['orginal_price']) * 100),
            'is_liked' => false,
        ];
    }
}
