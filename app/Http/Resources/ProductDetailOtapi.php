<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class ProductDetailOtapi extends JsonResource
{
    protected $amount;
    protected $percent;
    protected $provider;

    public function __construct($resource, $provider = 'taobao')
    {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->provider = $provider;
        $this->amount = Cache::remember('exchange_yuan', 60 * 60, function () {
            return Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;
        });
        $this->percent = Cache::remember('exchange_percent', 60 * 60, function () {
            return Exchanges::where('currency', 'product_percent')->first()->amount ?? 0;
        });
    }

    public function toArray($request)
    {
        $quantityRanges = [];

        foreach (isset($this['QuantityRanges']) ? $this['QuantityRanges'] : [] as $range) {
            $quantityRanges[] = [
                'minQuantity' => $range['MinQuantity'],
                'price' => round($this->addPercentToPrice($range['Price']['OriginalPrice'], $this->amount, $this->percent)),
            ];
        }
        return [
            'id' => $this['Id'],
            'title' => $this['Title'],
            'price' => round($this->addPercentToPrice($this['Price']['OriginalPrice'], $this->amount, $this->percent)),
            'quantityRanges' => $quantityRanges,
            // 'discounted_price' => round($this->addPercentToPrice($this['orginal_price'], $this->amount, $this->percent)),
            // 'discount_percentage' => round((($this['orginal_price'] - $this['price']) / $this['orginal_price']) * 100),
            'image' => $this['MainPictureUrl'],
            'quantity' => $this['MasterQuantity'],
            // 'seller_info' => $this['seller_info'],
            'provider' => $this['ProviderType'],
            'description' => str_replace(["\n", "\\"], '', $this['Description']) ?? '',
            'pictures' => $this->transformPictures($this['Pictures']),
            'location' => $this['Location']['City'] ?? 'China',
            'product_url' => $this['TaobaoItemUrl'],
            'videos' => $this['Videos'] ?? [],
            // 'skus1' => $this['skus']['sku'],
            'attributes' => $this->transformAttributes($this['Attributes'] ?? null),
            'configuredItems' => $this->transformConfiguredItems($this['ConfiguredItems'] ?? null),
            // 'prop_imgs' => $this['prop_imgs']['prop_img'],
            // 'props_list' => $this['props_list'],
            'compositions' => $this->transformCompositions($this['Attributes'] ?? null),
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
                'image_url' => preg_replace('/^\/\//', 'https://', $attribute['Url']) ?? null,
                'is_main' => 0,
            ];
        }, $pictures) : $pictures->map(function ($picture) {
            return [
                'image_url' => url('storage/' . $picture->image_url),
                'is_main' => $picture->is_main,
            ];
        })->toArray();
    }

    private function transformAttributes($attributes)
    {
        if (is_null($attributes) || empty($attributes)) {
            return [
                [
                    'vid' => "1.1:1.1",
                    'pid' => "1.1:property:default",
                    'propertyName' => "1.1:1.1",
                    'value' => "1.1:property:default",
                    'originalPropertyName' => "1.1:property:default",
                    'originalValue' => "1.1:1.1",
                    'isConfigurator' => true,
                    'imageUrl' => $this->resource['MainPictureUrl'],
                    'miniImageUrl' => $this->resource['MainPictureUrl'],
                ]
            ];
        }

        return array_values(array_filter(array_map(function ($attribute) {
            return [
                'pid' => $attribute['Pid'],
                'vid' => $attribute['Vid'],
                'propertyName' => $attribute['PropertyName'],
                'value' => $attribute['Value'],
                'originalPropertyName' => $attribute['OriginalPropertyName'],
                'originalValue' => $attribute['OriginalValue'],
                'isConfigurator' => $attribute['IsConfigurator'],
                'imageUrl' => $attribute['ImageUrl'] ?? null,
                'miniImageUrl' => $attribute['MiniImageUrl'] ?? null,
            ];
        }, $attributes), function ($attribute) {
            return $attribute['isConfigurator'] === true;
        }));
    }

    private function transformCompositions($compositions)
    {
        if (is_null($compositions) || empty($compositions)) {
            return [
                [
                    'vid' => "1.1:1.1",
                    'pid' => "1.1:property:default",
                    'propertyName' => "1.1:1.1",
                    'value' => "1.1:property:default",
                    'originalPropertyName' => "1.1:property:default",
                    'originalValue' => "1.1:1.1",
                    'isConfigurator' => false,
                    // 'imageUrl' => $this->resource['MainPictureUrl'],
                    // 'miniImageUrl' => $this->resource['MainPictureUrl'],
                ]
            ];
        }

        return array_values(array_filter(array_map(function ($composition) {
            return [
                'pid' => $composition['Pid'],
                'vid' => $composition['Vid'],
                'propertyName' => $composition['PropertyName'],
                'value' => $composition['Value'],
                'originalPropertyName' => $composition['OriginalPropertyName'],
                'originalValue' => $composition['OriginalValue'],
                'isConfigurator' => $composition['IsConfigurator'],
            ];
        }, $compositions), function ($composition) {
            return $composition['isConfigurator'] === false;
        }));
    }

    private function transformConfiguredItems($items)
    {
        if (is_null($items) || empty($items)) {
            return [
                [
                    'id' => $this->resource['Id'] . '0',
                    'quantity' => 1,
                    'salesCount' => 1,
                    'configurators' => [
                        [
                            'pid' => "1.1:property:default",
                            'vid' => "1.1:1.1"
                        ]
                    ],
                    'price' => round($this->addPercentToPrice($this->resource['Price']['OriginalPrice'], $this->amount, $this->percent)),
                    'quantityRanges' => []
                ]
            ];
        }

        return array_map(function ($item) {
            $quantityRanges = [];

            foreach (isset($item['QuantityRanges']) ? $item['QuantityRanges'] : [] as $range) {
                $quantityRanges[] = [
                    'minQuantity' => $range['MinQuantity'],
                    'price' => round($this->addPercentToPrice($range['Price']['OriginalPrice'], $this->amount, $this->percent)),
                ];
            }
            return [
                'id' => $item['Id'],
                'quantity' => $item['Quantity'],
                'salesCount' => $item['SalesCount'],
                'configurators' => $item['Configurators'],
                'price' => round($this->addPercentToPrice($item['Price']['OriginalPrice'], $this->amount, $this->percent)),
                'quantityRanges' => $quantityRanges ?? [],
            ];
        }, $items);
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
