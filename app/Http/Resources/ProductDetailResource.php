<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    protected $amount;
    protected $percent;
    protected $provider;

    public function __construct($resource, $provider = 'taobao')
    {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->provider = $provider;
        $this->amount = Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;
        $this->percent = Exchanges::where('currency', 'product_percent')->first()->amount ?? 0;
    }

    public function toArray($request)
    {
        return [
            'id' => $this['num_iid'],
            'title' => $this['title'],
            'price' => round($this->addPercentToPrice($this['price'], $this->amount, $this->percent)),
            'discounted_price' => round($this->addPercentToPrice($this['orginal_price'], $this->amount, $this->percent)),
            'discount_percentage' => round((($this['orginal_price'] - $this['price']) / $this['orginal_price']) * 100),
            'image' => preg_replace('/^\/\//', 'https://', $this['pic_url']),
            'quantity' => $this['num'],
            'seller_info' => $this['seller_info'],
            'provider' => $this->provider,
            'description' => str_replace(["\n", "\\"], '', $this['desc']),
            'pictures' => $this->transformPictures($this['item_imgs']),
            'location' => $this['location'] ?? 'China',
            'product_url' => $this['detail_url'],
            'videos' => $this['video'],
            // 'skus1' => $this['skus']['sku'],
            'skus' => $this->transformSkus($this['skus']['sku'] ?? null),
            'prop_imgs' => $this['prop_imgs']['prop_img'],
            'props_list' => $this['props_list'],
            'composition' => $this['props'],
            'is_liked' => false,
        ];
    }

    private function addPercentToPrice($priceInYuan, $amount, $percent = 0)
    {
        $priceInSum = $priceInYuan * $amount;
        $additionalAmount = $priceInSum * ($percent / 100);
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

    private function transformSkus($skus)
    {
        if (is_null($skus) || empty($skus)) {
            return [
                [
                    'price' => round($this->addPercentToPrice($this->resource['price'], $this->amount, $this->percent)),
                    'total_price' => 0,
                    'orginal_price' => round($this->addPercentToPrice($this->resource['orginal_price'] ?? 0, $this->amount, $this->percent)),
                    'properties' => "1.1:1.1",
                    'properties_name' => "1.1:Default",
                    'quantity' => $this->resource['num'],
                    'sku_id' => $this->resource['num_iid'] . 0,
                ]
            ];
        }

        return array_map(function ($sku) {
            return [
                'price' => round($this->addPercentToPrice($sku['price'], $this->amount, $this->percent)),
                'total_price' => round($this->addPercentToPrice($sku['total_price'], $this->amount, $this->percent)),
                'orginal_price' => round($this->addPercentToPrice($sku['orginal_price'] ?? 0, $this->amount, $this->percent)),
                'properties' => $sku['properties'],
                'properties_name' => $sku['properties_name'],
                'quantity' => $sku['quantity'],
                'sku_id' => $sku['sku_id'],
            ];
        }, $skus);
    }

    // private function transformAttributes($attributes)
    // {
    //     return array_map(function ($attribute) {
    //         return [
    //             'properties' => $attribute['properties'],
    //             'url' => preg_replace('/^\/\//', 'https://', $attribute['url']),
    //         ];
    //     }, $attributes);
    // }

    // private function findAttributeUrl($attributes, $colorId)
    // {
    //     // return $colorId;
    //     foreach ($attributes as $attribute) {
    //         if ($attribute['properties'] == $colorId) {
    //             return $attribute['url'];
    //         }
    //     }
    //     return null;
    // }

    // private function extractPropertiesDetails($properties_name)
    // {
    //     $properties = explode(';', $properties_name);
    //     $results = [];

    //     foreach ($properties as $property) {
    //         $parts = explode(':', $property);
    //         if (isset($parts[2]) && isset($parts[3])) {
    //             $results[] = [
    //                 'name' => $parts[2],
    //                 'value' => $parts[3],
    //                 'propertyFir' => $parts[0],
    //                 'propertySec' => $parts[1],
    //                 'displayName' => $parts[2],
    //                 'displayValue' => $parts[3],
    //             ];
    //         }
    //     }

    //     return $results;
    // }
}
