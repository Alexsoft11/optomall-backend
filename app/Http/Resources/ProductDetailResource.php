<?php

// namespace App\Http\Resources;

// use App\Models\Exchanges;
// use Illuminate\Http\Resources\Json\JsonResource;

// class ProductDetailResource extends JsonResource
// {
//     /**
//      * Transform the resource into an array.
//      *
//      * @param \Illuminate\Http\Request $request
//      * @return array
//      */
//     public function toArray($request)
//     {
//         $amount = Exchanges::where('currency', 'usd')->first()->amount ?? 12000;
//         $counter = 0;

//         // Retrieve original and discounted prices
//         $originalPrice = isset($this['Price']['ConvertedPriceWithoutSign']) ? round($this['Price']['ConvertedPriceWithoutSign']) * $amount : null;
//         $discountedPrice = isset($this['Promotions']) ? round($this['Promotions'][0]['Price']['ConvertedPriceList']['Internal']['Price']) * $amount : null;

//         // Calculate discount percentage
//         $discountPercentage = $originalPrice && $discountedPrice ? 100 - round((($discountedPrice / $originalPrice) * 100)) : null;

//         return [
//             'id' => $this['Id'] ?? null,
//             'title' => $this['Title'] ?? null,
//             'price' => $originalPrice,
//             'discounted_price' => $discountedPrice,
//             'discount_percentage' => $discountPercentage,
//             'image' => $this['MainPictureUrl'] ?? null,
//             'quantity' => $this['MasterQuantity'] ?? null,
//             'description' => $this['Description'] ?? null,
//             'pictures' => isset($this['Pictures']) ? array_map(function ($attribute) {
//                 return [
//                     'image_url' => $attribute['Url'] ?? null,
//                     'is_main' => $attribute['IsMain'] ?? null,
//                 ];
//             }, $this['Pictures']) : [],
//             'location' => $this['Location']['State'] ?? 'China',
//             'product_url' => $this['ExternalItemUrl'] ?? null,
//             'videos' => $this['Videos'] ?? [],
//             'colors' => isset($this['Attributes']) ? array_values(array_filter(array_map(function ($attribute) use (&$counter) {
//                 return [
//                     'id' => $counter++,
//                     'propertyName' => $attribute['PropertyName'] ?? null,
//                     'value' => $attribute['Value'] ?? null,
//                     'imageUrl' => $attribute['ImageUrl'] ?? null,
//                     'miniImageUrl' => $attribute['MiniImageUrl'] ?? null,
//                     'propertyId' => $attribute['Pid'] ?? null,
//                     'valueId' => $attribute['Vid'] ?? null,
//                 ];
//             }, $this['Attributes']), function ($color) {
//                 return ($color['propertyName'] === 'Rang bilan saralash' || $color['propertyName'] === 'tana rangi' || $color['propertyName'] === 'rang') && $color['imageUrl'] !== null;
//             })) : [],
//             'sizes' => isset($this['Attributes']) ? array_values(array_filter(array_map(function ($attribute) use (&$counter) {
//                 return [
//                     'id' => $counter++,
//                     'propertyName' => $attribute['PropertyName'] ?? null,
//                     'value' => $attribute['Value'] ?? null,
//                     'propertyId' => $attribute['Pid'] ?? null,
//                     'valueId' => $attribute['Vid'] ?? null,
//                 ];
//             }, $this['Attributes']), function ($size) {
//                 return $size['propertyName'] === "o'lcham" || $size['propertyName'] === 'saqlash' || $size['propertyName'] === "Ma'lumot balandligi";
//             })) : [],
//             'composition' => isset($this['Attributes']) ? array_values(array_filter(array_map(function ($attribute) use (&$counter) {
//                 return [
//                     'id' => $counter++,
//                     'propertyName' => $attribute['PropertyName'] ?? null,
//                     'value' => $attribute['Value'] ?? null,
//                 ];
//             }, $this['Attributes']), function ($composition) {
//                 return $composition['propertyName'] === "Moddiy tarkibiy qism";
//             })) : [],
//         ];
//     }
// }

namespace App\Http\Resources;

use App\Models\ProductColor;
use App\Models\ProductComposition;
use App\Models\ProductPicture;
use App\Models\ProductSize;
use App\Models\ProductVideo;
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
        $pictures = ProductPicture::where('product_id', $this->id)->get();
        $videos = ProductVideo::where('product_id', $this->id)->get();
        $colors = ProductColor::where('product_id', $this->id)->get();
        $sizes = ProductSize::where('product_id', $this->id)->get();
        $compositions = ProductComposition::where('product_id', $this->id)->get();

        return [
            'id' => $this->id,
            'product_category_id' => $this->product_category_id,
            'title' => $this->title,
            'price' => $this->price,
            'image' => isset($this->resource['image']) && strpos($this->resource['image'], 'https') === 0 ? $this->resource['image'] : 'https://backend.cjsaam.site/storage/' . ($this->resource['image'] ?? $this['image']),
            'location' => $this->location,
            'product_url' => $this->product_url,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'discount_percent' => $this->discount_percent,
            'discounted_price' => $this->discounted_price,
            'pictures' => $pictures,
            'videos' => $videos,
            'colors' => $colors->map(function ($color) {
                return [
                    'id' => $color->uuid,
                    'product_id' => $color->product_id,
                    'property_name' => $color->property_name,
                    'value' => $color->value,
                    'image_url' => $color->image_url,
                    'mini_image_url' => $color->mini_image_url,
                ];
            }),
            'sizes' => $sizes,
            'compositions' => $compositions,
        ];
    }
}
