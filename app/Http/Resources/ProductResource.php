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

        $amount = Exchanges::where('currency', 'usd')->first()->amount;

        return [
            'id' => $this['Id'],
            'title' => $this['Title'],
            'price' => $this['Price']['ConvertedPriceWithoutSign'] * $amount,
            'image' => $this['MainPictureUrl'],
        ];
    }
}
