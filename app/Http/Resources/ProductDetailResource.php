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
        $amount = Exchanges::where('currency', 'usd')->first()->amount;

        return [
            'id' => $this['Id'] ?? null,
            'title' => $this['Title'] ?? null,
            'price' => isset($this['Price']['ConvertedPriceWithoutSign']) ? $this['Price']['ConvertedPriceWithoutSign'] * $amount : null,
            'image' => $this['MainPictureUrl'] ?? null,
            'pictures' => isset($this['Pictures']) ? array_map(function ($attribute) {
                return [
                    'image_url' => $attribute['Url'] ?? null,
                    'is_main' => $attribute['IsMain'] ?? null,
                ];
            }, $this['Pictures']) : [],
            'location' => $this['Location']['State'], $this['Location']['City'] ?? null,
            'product_url' => $this['ExternalItemUrl'] ?? null,
            'videos' => $this['Videos'] ?? [],
            'colors' => isset($this['Attributes']) ? array_values(array_filter(array_map(function ($attribute) {
                return [
                    'propertyName' => $attribute['PropertyName'] ?? null,
                    'value' => $attribute['Value'] ?? null,
                    'imageUrl' => $attribute['ImageUrl'] ?? null,
                    'miniImageUrl' => $attribute['MiniImageUrl'] ?? null,
                ];
            }, $this['Attributes']), function ($color) {
                return $color['propertyName'] === 'rang' && $color['imageUrl'] !== null;
            })) : [],
            'sizes' => isset($this['Attributes']) ? array_values(array_filter(array_map(function ($attribute) {
                return [
                    'propertyName' => $attribute['PropertyName'] ?? null,
                    'value' => $attribute['Value'] ?? null,
                ];
            }, $this['Attributes']), function ($size) {
                return $size['propertyName'] === "o'lcham";
            })) : [],
            'composition' => isset($this['Attributes']) ? array_values(array_filter(array_map(function ($attribute) {
                return [
                    'propertyName' => $attribute['PropertyName'] ?? null,
                    'value' => $attribute['Value'] ?? null,
                ];
            }, $this['Attributes']), function ($composition) {
                return $composition['propertyName'] === "Moddiy tarkibiy qism";
            })) : [],
        ];
    }
}
