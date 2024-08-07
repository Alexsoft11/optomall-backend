<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetail extends JsonResource
{
    protected $lang;
    protected $amount;
    protected $percent;

    public function __construct($resource, $lang = null)
    {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->lang = $lang;
        $this->amount = Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;
        $this->percent = Exchanges::where('currency', 'product_percent')->first()->amount;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['num_iid'],
            'title' => $this['title'],
            'price' => round($this->addPercentToPrice($this['price'], $this->amount, $this->percent)),
            'discounted_price' => round($this->addPercentToPrice($this['orginal_price'], $this->amount, $this->percent)),
            'discount_percentage' => round((($this->resource['orginal_price'] - $this->resource['price']) / $this->resource['orginal_price']) * 100),
            'image' => preg_replace('/^\/\//', 'https://', $this['pic_url']),
            'quantity' => $this['num'],
            'description' => str_replace(["\n", "\\"], '', $this['desc']),
            'pictures' => $this->transformPictures($this['item_imgs']),
            'location' => $this['location'] ?? 'China',
            'product_url' => $this['detail_url'],
            'videos' => $this['video'],
            'colors_images' => $this['props_imgs']['prop_img'] ?? $this['prop_imgs']['prop_img'],
            'skus' => $this['skus']['sku'],
            'props_list' => $this['props_list'],
            'composition' =>  $this['props'],
            'is_liked' => false,
        ];
    }

    private function addPercentToPrice($priceInYuan, $amount, $percent = 0)
    {
        // Convert the price from yuan to sum
        $priceInSum = $priceInYuan * $amount;

        // Calculate the amount to add based on the percentage
        $additionalAmount = $priceInSum * ($percent / 100);

        // Return the price with the percentage added
        return $priceInSum + $additionalAmount;
    }

    private function transformPictures($pictures)
    {
        return is_array($pictures) ? array_map(function ($attribute) {
            return [
                'image_url' => preg_replace('/^\/\//', 'https://', $attribute['url']) ?? null,
                'is_main' => 0,
            ];
        }, $pictures) : $pictures->map(function ($picture) {
            return [
                'image_url' => url('storage/' . $picture->image_url),
                'is_main' => $picture->is_main,
            ];
        })->toArray();
    }
}
