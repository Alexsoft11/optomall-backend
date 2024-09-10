<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    protected $percent;
    protected $provider;

    public function __construct($resource, $provider)
    {
        parent::__construct($resource);
        $this->provider = $provider;
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

        // Convert price to float
        $price = floatval($this->resource['price']);

        // Check if promotion_price exists and convert it to float, otherwise set it to null
        $promotionPrice = isset($this->resource['promotion_price']) ? floatval($this->resource['promotion_price']) : null;

        // Calculate the actual prices
        $calculatedPrice = $this->calculatePrice($price, $amount);
        $calculatedPromotionPrice = $promotionPrice ? $this->calculatePrice($promotionPrice, $amount) : null;

        // // Calculate the discount percentage
        $discountPercent = $promotionPrice
            ? round((($price - $promotionPrice) / $price) * 100)
            : 0;

        return [
            'id' => $this->resource['num_iid'],
            'title' => $this->resource['title'],
            'price' => round($this->addPercentToPrice($calculatedPrice, $this->percent)),
            'discounted_price' => round($calculatedPromotionPrice
                ? $this->addPercentToPrice($calculatedPromotionPrice, $this->percent)
                : $this->addPercentToPrice($calculatedPrice, $this->percent)),
            'discount_percent' => $discountPercent,
            'image' => $this->resource['pic_url'],
            'product_url' => $this->resource['detail_url'],
            'provider' => $this->provider,
            'is_liked' => false,
        ];
    }

    private function calculatePrice($price, $amount)
    {
        return $price * $amount;
    }

    private function addPercentToPrice($price, $percent = 0)
    {
        $additionalAmount = $price * ($percent / 100);
        return $price + $additionalAmount;
    }
}
