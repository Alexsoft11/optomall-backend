<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
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
        $counter = 0;

        // Retrieve original and discounted prices
        $originalPrice = isset($this['Price']['ConvertedPriceWithoutSign']) ? round($this['Price']['ConvertedPriceWithoutSign']) * $amount : null;
        $discountedPrice = isset($this['Promotions']) ? round($this['Promotions'][0]['Price']['ConvertedPriceList']['Internal']['Price']) * $amount : null;

        // Calculate discount percentage
        $discountPercentage = $originalPrice && $discountedPrice ? 100 - round((($discountedPrice / $originalPrice) * 100)) : null;

        return [
            'id' => $this['Id'] ?? null,
            'title' => $this['Title'] ?? null,
            'price' => $originalPrice,
            'discounted_price' => $discountedPrice,
            'discount_percentage' => $discountPercentage,
            'image' => $this['MainPictureUrl'] ?? null,
            'quantity' => $this['MasterQuantity'] ?? null,
            'description' => $this['Description'] ?? null,
            'pictures' => isset($this['Pictures']) ? array_map(function ($attribute) {
                return [
                    'image_url' => $attribute['Url'] ?? null,
                    'is_main' => $attribute['IsMain'] ?? null,
                ];
            }, $this['Pictures']) : [],
            'location' => $this['Location']['State'] ?? 'China',
            'product_url' => $this['ExternalItemUrl'] ?? null,
            'videos' => $this['Videos'] ?? [],
            'colors' => isset($this['Attributes']) ? array_values(array_filter(array_map(function ($attribute) use (&$counter) {
                return [
                    'id' => $counter++,
                    'propertyName' => $attribute['PropertyName'] ?? null,
                    'value' => $attribute['Value'] ?? null,
                    'imageUrl' => $attribute['ImageUrl'] ?? null,
                    'miniImageUrl' => $attribute['MiniImageUrl'] ?? null,
                    'propertyId' => $attribute['Pid'] ?? null,
                    'valueId' => $attribute['Vid'] ?? null,
                ];
            }, $this['Attributes']), function ($color) {
                return ($color['propertyName'] === 'Rang bilan saralash' || $color['propertyName'] === 'tana rangi' || $color['propertyName'] === 'rang') && $color['imageUrl'] !== null;
            })) : [],
            'sizes' => isset($this['Attributes']) ? array_values(array_filter(array_map(function ($attribute) use (&$counter) {
                return [
                    'id' => $counter++,
                    'propertyName' => $attribute['PropertyName'] ?? null,
                    'value' => $attribute['Value'] ?? null,
                    'propertyId' => $attribute['Pid'] ?? null,
                    'valueId' => $attribute['Vid'] ?? null,
                ];
            }, $this['Attributes']), function ($size) {
                return $size['propertyName'] === "o'lcham" || $size['propertyName'] === 'saqlash';
            })) : [],
            'composition' => isset($this['Attributes']) ? array_values(array_filter(array_map(function ($attribute) use (&$counter) {
                return [
                    'id' => $counter++,
                    'propertyName' => $attribute['PropertyName'] ?? null,
                    'value' => $attribute['Value'] ?? null,
                ];
            }, $this['Attributes']), function ($composition) {
                return $composition['propertyName'] === "Moddiy tarkibiy qism";
            })) : [],
        ];
    }
}
