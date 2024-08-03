<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProductColor;
use App\Models\ProductComposition;
use App\Models\ProductPicture;
use App\Models\ProductSize;
use App\Models\ProductVideo;
use App\Models\User;

class ProductDetailTokenResource extends JsonResource
{
    protected $lang;
    protected $amount;

    public function __construct($resource, $lang = null)
    {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->lang = $lang;
        $this->amount = Exchanges::where('currency', 'usd')->first()->amount ?? 12000;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $isLiked = auth()->check() && auth()->user()->likedProducts()->where('product_id', $this->resource['num_iid'])->exists();

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
            'colors' => isset($this['props_imgs']) ? $this->transformAttributes($this['props_imgs']['prop_img']) : $this->transformAttributes($this['prop_imgs']['prop_img']),
            'sizes' => $this->transformSizes($this['skus']['sku']),
            'composition' =>  $this->transformCompositions($this['props'], ["brand", "pattern", "Collar", "basic style"]),
            'is_liked' => $isLiked,
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

        // Array of prefixes to consider
        $prefixes = ['color:', 'цвет:', 'rang:'];

        // Get the keys from props_list
        $propsListKeys = array_keys($this['props_list']);

        $transformedAttributes = array_filter(array_map(function ($attribute) use (&$counter, $propsListKeys, $prefixes) {
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

            // Get the value and remove the prefix if it exists
            $value = $matchingKey !== null ? $this['props_list'][$matchingKey] : null;
            if ($value !== null) {
                foreach ($prefixes as $prefix) {
                    if (stripos($value, $prefix) === 0) {
                        $value = preg_replace('/^' . preg_quote($prefix, '/') . '\s*/i', '', $value); // Remove prefix, case-insensitive
                        break;
                    }
                }
            }

            return [
                'id' => $counter++,
                'propertyName' => $attribute['PropertyName'] ?? null,
                'value' => $value,
                'image_url' => $attribute['url'] ?? null,
            ];
        }, $attributes));

        // Remove duplicate values
        $uniqueAttributes = array_unique($transformedAttributes, SORT_REGULAR);

        // Re-index the array
        return array_values($uniqueAttributes);
    }

    private function transformSizes($attributes)
    {
        $counter = 0;

        // Array of prefixes to consider
        $prefixes = ['size:', 'hajmi:', 'размер:'];

        // Filter props_list for entries with any of the prefixes
        $sizePropsList = array_filter($this['props_list'], function ($value) use ($prefixes) {
            foreach ($prefixes as $prefix) {
                if (stripos($value, $prefix) === 0) {
                    return true;
                }
            }
            return false;
        });

        // Get the keys from sizePropsList
        $sizePropsListKeys = array_keys($sizePropsList);

        $transformedAttributes = array_filter(array_map(function ($attribute) use (&$counter, $sizePropsListKeys, $prefixes) {
            // Ensure properties is a string
            $properties = explode(';', (string)$attribute['properties']);

            // Find the first key from sizePropsListKeys that matches any property
            $matchingKey = null;
            foreach ($properties as $property) {
                if (in_array($property, $sizePropsListKeys)) {
                    $matchingKey = $property;
                    break;
                }
            }

            // Get the value and remove the prefix if it exists
            $value = $matchingKey !== null ? $this['props_list'][$matchingKey] : null;
            if ($value !== null) {
                foreach ($prefixes as $prefix) {
                    if (stripos($value, $prefix) === 0) {
                        $value = preg_replace('/^' . preg_quote($prefix, '/') . '\s*/i', '', $value); // Remove prefix, case-insensitive
                        break;
                    }
                }
            }

            return [
                'id' => $counter++,
                'quantity' => $attribute['quantity'],
                'price' => round($attribute['orginal_price'] ?? $attribute['price'] * $this->amount),
                'value' => $value,
                'sku_id' => $attribute['sku_id'],
            ];
        }, $attributes));

        // Remove entries with null values
        $transformedAttributes = array_filter($transformedAttributes, function ($attribute) {
            return $attribute['value'] !== null;
        });

        // Sort by value
        usort($transformedAttributes, function ($a, $b) {
            return strcmp($a['value'], $b['value']);
        });

        // Remove duplicate values
        $uniqueAttributes = [];
        foreach ($transformedAttributes as $attribute) {
            if (!isset($uniqueAttributes[$attribute['value']])) {
                $uniqueAttributes[$attribute['value']] = $attribute;
            }
        }

        // Re-index the array
        return array_values($uniqueAttributes);
    }



    private function transformCompositions($attributes, $propertyNames)
    {
        $counter = 0;

        // Transform and filter attributes
        $transformedAttributes = array_filter(array_map(function ($attribute) use (&$counter) {
            return [
                'id' => $counter++,
                'propertyName' => $attribute['name'] ?? null,
                'value' => $attribute['value'] ?? null,
            ];
        }, $attributes), function ($attribute) use ($propertyNames) {
            return in_array($attribute['propertyName'], $propertyNames);
        });

        // Re-index the array
        return array_values($transformedAttributes);
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