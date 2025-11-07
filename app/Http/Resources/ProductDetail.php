<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetail extends JsonResource
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
            'seller_id' => $this['seller_info']['user_num_id'],
            'provider' => $this->provider,
            'description' => str_replace(["\n", "\\"], '', $this['desc']),
            'pictures' => $this->transformPictures($this['item_imgs']),
            'location' => $this['location'] ?? 'China',
            'product_url' => $this['detail_url'],
            'videos' => $this['video'],
            'skus' => $this->transformSkus($this['prop_imgs']['prop_img'] ?? $this['prop_imgs']['prop_img'], $this['skus']['sku']),
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

    private function transformSkus($propImgs, $skus)
    {
        $attributes = $this->transformAttributes($propImgs);

        return array_map(function ($sku) use ($attributes) {
            // return $attributes;
            $propertyDetails = $this->extractPropertiesDetails($sku['properties_name']);
            $propertiesArray = [];

            foreach ($propertyDetails as $detail) {
                $imageUrl = $this->findAttributeUrl($attributes, $detail['propertyFir'] . ":" . $detail['propertySec']);
                $propertiesArray[] = [
                    'name' => $detail['name'],
                    'value' => $detail['value'],
                    'displayName' => $detail['displayName'],
                    'displayValue' => $detail['displayValue'],
                    'imageUrl' => $imageUrl,
                    'property_id' => $detail['propertyFir'] . ":" . $detail['propertySec'],
                ];
            }

            return [
                'quantity' => $sku['quantity'],
                'sku_id' => $sku['sku_id'],
                'price' => round($this->addPercentToPrice($sku['price'], $this->amount, $this->percent)),
                'properties_ids' => $sku['properties'],
                'properties' => $propertiesArray,
            ];
        }, $skus);
    }

    private function transformAttributes($attributes)
    {
        return array_map(function ($attribute) {
            return [
                'properties' => $attribute['properties'],
                'url' => preg_replace('/^\/\//', 'https://', $attribute['url']),
            ];
        }, $attributes);
    }

    private function findAttributeUrl($attributes, $colorId)
    {
        // return $colorId;
        foreach ($attributes as $attribute) {
            if ($attribute['properties'] == $colorId) {
                return $attribute['url'];
            }
        }
        return null;
    }

    private function extractPropertiesDetails($properties_name)
    {
        $properties = explode(';', $properties_name);
        $results = [];

        foreach ($properties as $property) {
            $parts = explode(':', $property);
            if (isset($parts[2]) && isset($parts[3])) {
                $results[] = [
                    'name' => $parts[2],
                    'value' => $parts[3],
                    'propertyFir' => $parts[0],
                    'propertySec' => $parts[1],
                    'displayName' => $parts[2],
                    'displayValue' => $parts[3],
                ];
            }
        }

        return $results;
    }
}
