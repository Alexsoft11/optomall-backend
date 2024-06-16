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

        $promotionPriceExists = isset($this->resource['PromotionPrice']);
        $promotionPricePercent = null;

        if ($promotionPriceExists) {
            foreach ($this->resource['PromotionPricePercent'] as $percent) {
                if ($percent['CurrencyCode'] == 'USD') {
                    $promotionPricePercent = $percent['Percent'];
                    break;
                }
            }
        }

        return [
            'id' => $this->resource['Id'],
            'title' => $this->resource['Title'],
            'price' => $this->resource['Price']['ConvertedPriceWithoutSign'] * $amount,
            'image' => $this->resource['MainPictureUrl'],
            'discounted_price' => $promotionPriceExists ? $this->resource['PromotionPrice']['ConvertedPriceList']['Internal']['Price'] * $amount : null,
            'discount_percent' => $promotionPricePercent,
        ];
    }
}
