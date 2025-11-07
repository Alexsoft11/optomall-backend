<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
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
            'id' => $this['Id'] ?? $this['id'],
            'name' => $this['Name'] ?? $this['name'],
            'parentId' => $this['ParentId'] ?? $this['category_id'],
            'provider' => $this['ProviderType'],
            'externalId' => $this['ExternalId'],
        ];
    }
}
