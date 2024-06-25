<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $itemScore = null;
        $sold = null;
        if (isset($this['Features'])) {
            foreach ($this['Features'] as $attribute) {
                if (isset($attribute['Id']) && $attribute['Id'] === 'ItemScore') {
                    $itemScore = $attribute['Value'] ?? null;
                }
                if (isset($attribute['Id']) && $attribute['Id'] === 'fans') {
                    $sold = $attribute['Value'] ?? null;
                }
            }
        }

        return [
            'rating' => $itemScore,
            'sold' => $sold,
        ];
    }
}
