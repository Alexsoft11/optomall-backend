<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this['catalog_id'] ?? $this['id'],
            'name' => $this['name'],
            'image' => is_null($this['image']) ? null : url('storage/' . $this['image']),
            'is_active' => $this['is_active'],
        ];
    }
}
