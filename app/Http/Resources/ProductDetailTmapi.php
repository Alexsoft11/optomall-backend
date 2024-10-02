<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailTmapi extends JsonResource
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

        $quantityRanges = [];

        foreach (isset($this['priceRange']) ? $this['priceRange'] : [] as $range) {
            $quantityRanges[] = [
                'minQuantity' => $range[0],
                'price' => round($this->addPercentToPrice($range[1], $this->amount, $this->percent)),
            ];
        }

        usort($quantityRanges, function ($a, $b) {
            return $a['minQuantity'] <=> $b['minQuantity'];
        });
        return [
            'id' => $this['item_id'],
            'title' => $this['title'],
            'price' => round($this->addPercentToPrice($this['price_info']['price'] ?? $this['orginal_price'], $this->amount, $this->percent)),
            'discounted_price' => round($this->addPercentToPrice($this['price_info']['discount_price'] ?? 0, $this->amount, $this->percent)),
            'discount_percentage' => round(((intval($this['price_info']['price']) - intval($this['price_info']['discount_price'] ?? 0)) / 1) * 100),
            'quantityRanges' => $quantityRanges,
            'image' => preg_replace('/^\/\//', 'https://', $this['main_imgs'][0]),
            'quantity' => $this['sale_count'] ?? 0,
            'seller_info' => $this['shop_info'] ?? [],
            'provider' => $this->provider,
            'description' => str_replace(["\n", "\\"], '', $this['desc'] ?? ''),
            'pictures' => $this->transformPictures($this['main_imgs']),
            'location' => $this['location'] ?? 'China',
            'product_url' => $this['product_url'],
            'videos' => $this['video_url'],
            // 'skus1' => $this['skus']['sku'],
            'skus' => $this->transformSkus($this['skus'] ?? null),
            'prop_imgs' => [],
            'props_list' => $this['sku_props'],
            'composition' => $this['product_props'],
            'is_liked' => false,
        ];
    }

    private function addPercentToPrice($priceInYuan, $amount, $percent = 0)
    {
        $priceInSum = intval($priceInYuan) * intval($amount);
        $additionalAmount = $priceInSum * ($percent / 100);
        return $priceInSum + $additionalAmount;
    }

    private function transformPictures($pictures)
    {
        return is_array($pictures) ? array_map(function ($attribute) {
            return [
                'image_url' => preg_replace('/^\/\//', 'https://', $attribute) ?? null,
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
                    'price' => round($this->addPercentToPrice($this->resource['price_info']['price'], $this->amount, $this->percent)),
                    'total_price' => 0,
                    'orginal_price' => round($this->addPercentToPrice($this->resource['price_info']['price'] ?? 0, $this->amount, $this->percent)),
                    'properties' => "1.1:1.1",
                    'properties_name' => "1.1:property:default",
                    'quantity' => $this->resource['num'],
                    'sku_id' => $this->resource['num_iid'] . 0,
                ]
            ];
        }

        return array_map(function ($sku) {
            return [
                'price' => round($this->addPercentToPrice($sku['sale_price'], $this->amount, $this->percent)),
                'total_price' => round($this->addPercentToPrice($sku['sale_price'], $this->amount, $this->percent)),
                'orginal_price' => round($this->addPercentToPrice($sku['orginal_price'] ?? 0, $this->amount, $this->percent)),
                'properties' => $sku['props_ids'],
                'properties_name' => $sku['props_names'],
                'quantity' => $sku['stock'],
                'sku_id' => $sku['skuid'],
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
