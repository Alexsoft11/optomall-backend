<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    protected $percent;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->percent = Exchanges::where('currency', 'product_percent')->first()->amount ?? 0;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $amount = Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;

        $originalPrice = $this->resource['orginal_price'] ?? null;
        $price = $this->resource['price'];
        $promotionPrice = $this->resource['promotion_price'] ?? null;

        // Calculate the actual prices
        $calculatedPrice = $this->calculatePrice($price, $amount);
        $calculatedPromotionPrice = $promotionPrice ? $this->calculatePrice($promotionPrice, $amount) : null;

        // Calculate the discount percentage
        $discountPercent = $originalPrice
            ? round((($originalPrice - ($promotionPrice ?? $price)) / $originalPrice) * 100)
            : 0;

        return [
            'id' => $this->resource['num_iid'],
            'title' => $this->resource['title'],
            'price' => isset($originalPrice)
                ? round($this->addPercentToPrice($originalPrice, $amount, $this->percent))
                : round($this->addPercentToPrice($calculatedPrice, $amount, $this->percent)),
            'discounted_price' => round($calculatedPromotionPrice
                ? $this->addPercentToPrice($calculatedPromotionPrice, $amount, $this->percent)
                : $this->addPercentToPrice($calculatedPrice, $amount, $this->percent)),
            'discount_percent' => $discountPercent,
            'image' => $this->resource['pic_url'],
            'is_liked' => false,
        ];
    }

    private function calculatePrice($price, $amount)
    {
        return $price * $amount;
    }

    private function addPercentToPrice($price, $amount, $percent = 0)
    {
        // The price is already in the target currency
        $additionalAmount = $price * ($percent / 100);
        return $price + $additionalAmount;
    }
}
