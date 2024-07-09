<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProductColor;
use App\Models\ProductComposition;
use App\Models\ProductPicture;
use App\Models\ProductSize;
use App\Models\ProductVideo;

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
        $productId = 808807124248;

        $pictures = ProductPicture::where('product_id', $productId)->get();
        $videos = ProductVideo::where('product_id', $productId)->get();
        $colors = ProductColor::where('product_id', $productId)->get();
        $sizes = ProductSize::where('product_id', $productId)->get();
        $compositions = ProductComposition::where('product_id', $productId)->get();

        $amount = Exchanges::where('currency', 'usd')->first()->amount ?? 12000;
        $counter = 0;

        // Retrieve original and discounted prices
        $originalPrice = isset($this['Price']['ConvertedPriceWithoutSign'])
            ? round($this['Price']['ConvertedPriceWithoutSign']) * $amount
            : null;
        $discountedPrice = isset($this['Promotions'])
            ? round($this['Promotions'][0]['Price']['ConvertedPriceList']['Internal']['Price']) * $amount
            : null;

        // Calculate discount percentage
        $discountPercentage = $originalPrice && $discountedPrice
            ? 100 - round((($discountedPrice / $originalPrice) * 100))
            : null;

        return [
            'id' => $this['Id'] ?? $productId,
            'title' => $this['Title'] ?? $this->title,
            'price' => $originalPrice ?? $this->price,
            'discounted_price' => $discountedPrice,
            'discount_percentage' => $discountPercentage,
            'image' => $this['MainPictureUrl'] ?? $this['image'],
            'quantity' => $this['MasterQuantity'] ?? $this->quantity,
            'description' => $this['Description'] ?? $this->description,
            'pictures' => isset($this['Pictures']) ? $this->transformPictures($this['Pictures']) : $this->transformPictures($pictures),
            'location' => $this['Location']['State'] ?? 'China',
            'product_url' => $this['ExternalItemUrl'] ?? $this->product_url,
            'videos' => $this['Videos'] ?? $videos,
            'colors' => isset($this['Attributes']) ? $this->transformAttributes($this['Attributes'], ['Rang bilan saralash', 'tana rangi', 'rang']) : $this->transformEloquentAttributes($colors),
            'sizes' => isset($this['Attributes']) ? $this->transformSizes($this['Attributes'], ["o'lcham", 'saqlash', "Ma'lumot balandligi"]) : $sizes,
            'composition' => isset($this['Attributes']) ? $this->transformCompositions($this['Attributes'], ["Moddiy tarkibiy qism"]) : $compositions,
        ];
    }

    private function transformPictures($pictures)
    {
        return is_array($pictures) ? array_map(function ($attribute) {
            return [
                'image_url' => $attribute['Url'] ?? null,
                'is_main' => $attribute['IsMain'] ?? null,
            ];
        }, $pictures) : $pictures->map(function ($picture) {
            return [
                'image_url' => $picture->image_url,
                'is_main' => $picture->is_main,
            ];
        })->toArray();
    }

    private function transformAttributes($attributes, $propertyNames)
    {
        $counter = 0;
        return array_values(array_filter(array_map(function ($attribute) use (&$counter) {
            return [
                'id' => $counter++,
                'propertyName' => $attribute['PropertyName'] ?? null,
                'value' => $attribute['Value'] ?? null,
                'imageUrl' => $attribute['ImageUrl'] ?? null,
                'miniImageUrl' => $attribute['MiniImageUrl'] ?? null,
                'propertyId' => $attribute['Pid'] ?? null,
                'valueId' => $attribute['Vid'] ?? null,
            ];
        }, $attributes), function ($attribute) use ($propertyNames) {
            return in_array($attribute['propertyName'], $propertyNames) && $attribute['imageUrl'] !== null;
        }));
    }

    private function transformSizes($attributes, $propertyNames)
    {
        $counter = 0;
        return array_values(array_filter(array_map(function ($attribute) use (&$counter) {
            return [
                'id' => $counter++,
                'propertyName' => $attribute['PropertyName'] ?? null,
                'value' => $attribute['Value'] ?? null,
                'propertyId' => $attribute['Pid'] ?? null,
                'valueId' => $attribute['Vid'] ?? null,
            ];
        }, $attributes), function ($attribute) use ($propertyNames) {
            return in_array($attribute['propertyName'], $propertyNames);
        }));
    }

    private function transformCompositions($attributes, $propertyNames)
    {
        $counter = 0;
        return array_values(array_filter(array_map(function ($attribute) use (&$counter) {
            return [
                'id' => $counter++,
                'propertyName' => $attribute['PropertyName'] ?? null,
                'value' => $attribute['Value'] ?? null,
            ];
        }, $attributes), function ($attribute) use ($propertyNames) {
            return in_array($attribute['propertyName'], $propertyNames);
        }));
    }

    private function transformEloquentAttributes($attributes)
    {
        return $attributes->map(function ($attribute) {
            return [
                'id' => $attribute->id ?? $attribute->color_id ?? $attribute->size_id,
                'product_id' => $attribute->product_id,
                'property_name' => $attribute->property_name ?? null,
                'value' => $attribute->value ?? null,
                'image_url' => $attribute->image_url ?? null,
                'mini_image_url' => $attribute->mini_image_url ?? null,
            ];
        })->toArray();
    }
}


// namespace App\Http\Resources;

// use App\Models\ProductColor;
// use App\Models\ProductComposition;
// use App\Models\ProductPicture;
// use App\Models\ProductSize;
// use App\Models\ProductVideo;
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
//         $pictures = ProductPicture::where('product_id', $this->id)->get();
//         $videos = ProductVideo::where('product_id', $this->id)->get();
//         $colors = ProductColor::where('product_id', $this->id)->get();
//         $sizes = ProductSize::where('product_id', $this->id)->get();
//         $compositions = ProductComposition::where('product_id', $this->id)->get();

//         return [
//             'id' => $this->id,
//             'product_category_id' => $this->product_category_id,
//             'title' => $this->title,
//             'price' => $this->price,
//             'image' => isset($this->resource['image']) && strpos($this->resource['image'], 'https') === 0 ? $this->resource['image'] : 'https://backend.cjsaam.site/storage/' . ($this->resource['image'] ?? $this['image']),
//             'location' => $this->location,
//             'product_url' => $this->product_url,
//             'description' => $this->description,
//             'quantity' => $this->quantity,
//             'discount_percent' => $this->discount_percent,
//             'discounted_price' => $this->discounted_price,
//             'pictures' => $pictures,
//             'videos' => $videos,
//             'colors' => $colors->map(function ($color) {
//                 return [
//                     'id' => $color->color_id,
//                     'product_id' => $color->product_id,
//                     'property_name' => $color->property_name,
//                     'value' => $color->value,
//                     'image_url' => $color->image_url,
//                     'mini_image_url' => $color->mini_image_url,
//                 ];
//             }),
//             'sizes' => $sizes->map(function ($size) {
//                 return [
//                     'id' => $size->size_id,
//                     'product_id' => $size->product_id,
//                     'property_name' => $size->property_name,
//                     'value' => $size->value,
//                 ];
//             }),
//             'compositions' => $compositions,
//         ];
//     }
// }
