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
            'subcategories' => SubcategoryResource::collection($this->subcategories),
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
            'name' => $this->{"subcategory_name_$lang"},
            'slug' => $this->unique,
            'items' => $this->items->map(function ($item) use ($lang) {
                return [
                    'name' => $item->{"subcategory_$lang"},
                    'slug' => $item->unique,
                ];
            }),
        ];
    }
}
