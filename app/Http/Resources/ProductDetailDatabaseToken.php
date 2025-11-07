<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProductColor;
use App\Models\ProductComposition;
use App\Models\ProductPicture;
use App\Models\ProductSize;

class ProductDetailDatabaseToken extends JsonResource
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
        $pictures = ProductPicture::where('product_id', $this['id'])->get();
        $colors = ProductColor::where('product_id', $this['id'])->get();
        $sizes = ProductSize::where('product_id', $this['id'])->get();
        $compositions = ProductComposition::where('product_id', $this['id'])->get();

        $amount = Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;
        $isLiked = auth()->check() && auth()->user()->likedProducts()->where('product_id', $this['id'])->exists();
        return [
            'id' => $this['id'],
            'title' => $this['title_' . $this->lang],
            'price' => $this->price,
            'discounted_price' => 0,
            'discount_percentage' => 0,
            'image' =>  url('storage/' . $this['image']),
            'quantity' => $this->quantity ?? 1,
            'description' => $this['description_' . $this->lang],
            'pictures' => $this->transformPictures($pictures) ?? [],
            'location' => 'China',
            'product_url' => $this->product_url,
            'videos' => [],
            'colors' => $this->transformEloquentAttributes($colors) ?? [],
            'sizes' => $sizes ?? [],
            'composition' => $compositions ?? [],
            'is_liked' => $isLiked,
        ];
    }

    private function transformPictures($pictures)
    {
        $pictures->map(function ($picture) {
            return [
                'image_url' => url('storage/' . $picture->image_url),
                'is_main' => $picture->is_main,
            ];
        })->toArray();
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
