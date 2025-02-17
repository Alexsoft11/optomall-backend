<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PrImage;
use App\Models\SkuProp;
use App\Models\PrComposition;
use App\Models\PrDescription;
use App\Models\Skus;

class ProductDetailDatabase extends JsonResource
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
        $images = PrImage::where('product_local_id', $this->id)->get();
        $descriptions = PrDescription::where('product_local_id', $this->id)->get();
        $skuProps = SkuProp::where('product_local_id', $this->id)->with('sku_values')->get();
        $sku = Skus::where('product_local_id', $this->id)->get();
        $compositions = PrComposition::where('product_local_id', $this->id)->get();


        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'discounted_price' => 0,
            'discount_percentage' => 0,
            'quantityRanges' => [],
            'image' => url('storage/' . $images->where('is_main', true)->first()->image_url) ?? null,
            'quantity' => $this->quantity ?? 1,
            'seller_info' => [],
            'delivery_info' => [],
            'provider' => 'local',
            'description' => $this->transformDescriptions($descriptions) ?? [],
            'pictures' => $this->transformImages($images) ?? [],
            'location' => 'Uzbekistan',
            'product_url' => '',
            'sku_price_range' => [],
            'skus' => $sku,
            'prop_imgs' => [],
            'props_list' => $this->transformSkuProps($skuProps) ?? [],
            'composition' => $this->transformCompositions($compositions) ?? [],
            'is_liked' => false,
        ];
    }

    private function transformImages($images)
    {
        return $images->map(function ($image) {
            return [
                'image_url' => url('storage/' . $image->image_url),
                'is_main' => $image->is_main,
            ];
        })->toArray();
    }

    private function transformDescriptions($descriptions)
    {
        return $descriptions->flatMap(function ($description) {
            return collect($description->image)->map(function ($image) {
                return url('storage/' . $image);
            });
        })->toArray();
    }

    private function transformCompositions($compositions)
    {
        return $compositions->map(function ($composition) {
            return [
                $composition->composition_name => $composition->composition,
            ];
        })->toArray();
    }


    private function transformSkuProps($skuProps)
    {
        return $skuProps->map(function ($skuProp) {
            return [
                'prop_id' => $skuProp->pid,
                'prop_name' => $skuProp->prop_name,
                'values' => $skuProp->sku_values->map(function ($value) {
                    return [
                        'id' => $value->vid,
                        'name' => $value->name,
                        'imageUrl' => $value->imageUrl ? url('storage/' . $value->imageUrl) : null,
                    ];
                }),
            ];
        })->toArray();
    }
}
