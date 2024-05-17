<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\Translator;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $translator = new Translator();
        $translatedTitle = $translator->translate($this['title'], 'uz');
        $translatedDescription = $translator->translate($this['description'], 'uz');
        
        $decodedDescription = html_entity_decode($translatedDescription);
        $decodedTitle = html_entity_decode($translatedTitle);
        $currency = Exchanges::where('currency', 'uzs')->first()->amount;
        
        return [
            'id' => $this['id'],
            'title' => $decodedTitle,
            'price' => $this['price'] * $currency,
            'description' => $decodedDescription,
        ];
    }
}