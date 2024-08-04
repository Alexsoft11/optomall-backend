<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductTokenResource extends JsonResource
{

    protected $percent;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->percent = Exchanges::where('currency', 'product_percent')->first()->amount;
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
        // $isLiked = auth()->check() && auth()->user()->likedProducts()->where('product_id', $this->resource['num_iid'])->exists();
        $price = $this->resource['price'] * $amount;
        $promotionPrice = array_key_exists('promotion_price', $this->resource) ? $this->resource['promotion_price'] : null;

        return [
            'id' => $this->resource['num_iid'],
            'title' => $this->resource['title'],
            'price' => isset($originalPrice)
                ? round($this->addPercentToPrice($originalPrice, $amount, $this->percent))
                : round($this->addPercentToPrice($price, $amount, $this->percent)),
            'discounted_price' => round(isset($promotionPrice)
                ? $this->addPercentToPrice($promotionPrice, $amount, $this->percent)
                : $this->addPercentToPrice($price, $amount, $this->percent)),
            'discount_percent' => isset($originalPrice)
                ? round((($originalPrice - ($promotionPrice ?? $this->resource['price'])) / $originalPrice) * 100)
                : round((($this->resource['price'] - ($promotionPrice ?? $this->resource['price'])) / $this->resource['price']) * 100),
            'image' => $this->resource['pic_url'],
            'is_liked' => false,
        ];
    }

    private function addPercentToPrice($price, $amount, $percent = 0)
    {
        // Convert the price to the target currency
        $priceInTargetCurrency = $price * $amount;

        // Calculate the amount to add based on the percentage
        $additionalAmount = $priceInTargetCurrency * ($percent / 100);

        // Return the price with the percentage added
        return $priceInTargetCurrency + $additionalAmount;
    }
}
