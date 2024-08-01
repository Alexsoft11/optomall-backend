<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProductColor;
use App\Models\ProductComposition;
use App\Models\ProductPicture;
use App\Models\ProductSize;

class ProductDetailResource extends JsonResource
{
    protected $lang;

    public function __construct($resource, $lang = null)
    {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->lang = $lang;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $amount = Exchanges::where('currency', 'usd')->first()->amount ?? 12000;

        return [
            'id' => $this['num_iid'],
            'title' => $this['title'],
            'price' => round($this['orginal_price']),
            'discounted_price' => round($this['price']),
            'discount_percentage' => round((($this->resource['orginal_price'] - $this->resource['price']) / $this->resource['orginal_price']) * 100),
            'image' => $this['pic_url'],
            'quantity' => $this['num'],
            'description' => str_replace(["\n", "\\"], '', $this['desc']),
            'pictures' => $this->transformPictures($this['item_imgs']),
            'location' => $this['location'] ?? 'China',
            'product_url' => $this['detail_url'],
            'videos' => $this['video'],
            'colors' =>  $this->transformAttributes($this['props_imgs']['prop_img']),
            // 'sizes' => isset($this['Attributes']) ? $this->transformSizes($this['Attributes'], ["o'lcham", 'saqlash', "Ma'lumot balandligi"]) : $sizes,
            // 'composition' => isset($this['Attributes']) ? $this->transformCompositions($this['Attributes'], ["Moddiy tarkibiy qism"]) : $compositions,
            'is_liked' => false,
        ];
    }

    private function transformPictures($pictures)
    {
        return is_array($pictures) ? array_map(function ($attribute) {
            return [
                'image_url' => $attribute['url'] ?? null,
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
        $counter = 0;
        // Get the keys from props_list
        $propsListKeys = array_keys($this['props_list']);

        return array_values(array_filter(array_map(function ($attribute) use (&$counter, $propsListKeys) {
            // Ensure properties is a string
            $property = (string) $attribute['properties'];

            // Find the first key from propsListKeys that matches the property
            $matchingKey = null;
            foreach ($propsListKeys as $key) {
                // Convert key to string for comparison
                if ((string) $key === $property) {
                    $matchingKey = $key;
                    break;
                }
            }

            // Get the value and remove "color:" prefix if it exists
            $value = $matchingKey !== null ? $this['props_list'][$matchingKey] : null;
            if ($value !== null) {
                $value = preg_replace('/^color:\s*/i', '', $value); // Remove "color:" prefix, case-insensitive
            }

            return [
                'id' => $counter++,
                'propertyName' => $attribute['PropertyName'] ?? null,
                'value' => $value,
                'image_url' => $attribute['url'] ?? null,
            ];
        }, $attributes)));
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
                'image_url' => url('storage/' . $attribute->image_url) ?? null,
                'mini_image_url' => url('storage/' . $attribute->mini_image_url) ?? null,
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
