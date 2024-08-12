<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lang = $request->query('lang', 'en');

        return [
            'name' => $this->{"name_$lang"},
            'slug' => $this->unique,
            'subcategories' => SubcategoryResource::collection($this->subcategories->where('is_active', true)),
        ];
    }
}

class SubcategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lang = $request->query('lang', 'en');

        return [
            'id' => $this->unique_id,
            'name' => $this->{"subcategory_name_$lang"},
            'slug' => $this->unique,
            'items' => $this->items->map(function ($item) use ($lang) {
                return [
                    'id' => $item->unique_id,
                    'is_show' => $item->is_show,
                    'name' => $item->{"subcategory_$lang"},
                    'slug' => $item->unique,
                    'hints' => $item->hints->pluck('name')->toArray(),
                ];
            }),
        ];
    }
}
