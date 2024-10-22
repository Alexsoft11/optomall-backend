<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Http;

class ProductResource extends JsonResource
{
    protected $percent;
    protected $provider;
    protected $lang;

    public function __construct($resource, $provider, $lang)
    {
        parent::__construct($resource);
        $this->provider = $provider;
        $this->lang = $lang;
        $this->percent = Exchanges::where('currency', 'product_percent')->first()->amount ?? 0;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $amount = Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;

        // Convert price to float
        $price = floatval($this->resource['price']);

        // Check if promotion_price exists and convert it to float, otherwise set it to null
        // Check if promotion_price exists and convert it to float, otherwise set it to null
        $promotionPrice = isset($this->resource['promotion_price']) && $this->resource['promotion_price'] !== null
            ? floatval($this->resource['promotion_price'])
            : null;

        // Calculate the actual prices
        $calculatedPrice = $this->calculatePrice($price, $amount);
        $calculatedPromotionPrice = $promotionPrice ? $this->calculatePrice($promotionPrice, $amount) : null;

        // Calculate the discount percentage
        $discountPercent = $promotionPrice
            ? round((($price - $promotionPrice) / $price) * 100)
            : 0;

        // Translate the title
        // $translatedTitle = $this->translateText($this->resource['title'], $this->lang);

        return [
            'id' => $this->resource['num_iid'] ?? $this->resource['item_id'],
            'title' => $this->resource['title'], // Return translated title
            'price' => round($this->addPercentToPrice($calculatedPrice, $this->percent)),
            'discounted_price' => round($calculatedPromotionPrice
                ? $this->addPercentToPrice($calculatedPromotionPrice, $this->percent)
                : $this->addPercentToPrice($calculatedPrice, $this->percent)),
            'discount_percent' => $discountPercent,
            'image' => $this->resource['pic_url'] ?? $this->resource['img'],
            'product_url' => $this->resource['detail_url'] ?? $this->resource['product_url'],
            'provider' => $this->provider,
            'is_liked' => false,
        ];
    }

    private function calculatePrice($price, $amount)
    {
        return $price * $amount;
    }

    private function addPercentToPrice($price, $percent = 0)
    {
        $additionalAmount = $price * ($percent / 100);
        return $price + $additionalAmount;
    }

    // private function translateText($text, $lang = 'en')
    // {
    //     // Prepare the translation data
    //     $data = [
    //         'from' => 'auto',
    //         'to' => $lang, // Change the target language if needed
    //         'text' => $text
    //     ];

    //     // Make the translation API call
    //     $translateUrl = "https://aibit-translator.p.rapidapi.com/api/v1/translator/text";
    //     try {
    //         $response = Http::withHeaders([
    //             'Content-Type' => 'application/json',
    //             'x-rapidapi-host' => 'aibit-translator.p.rapidapi.com',
    //             'x-rapidapi-key' => '74e939e778mshea2f00b1b0170dap1cc6eajsnf05e5c9be03a', // Your API key
    //         ])->post($translateUrl, $data);

    //         $result = $response->json();

    //         // Return the translated text or original if not available
    //         return $result['trans'] ?? $text;
    //     } catch (\Exception $e) {
    //         \Log::error('Translation API error: ' . $e->getMessage());
    //         return $text; // Return original text if translation fails
    //     }
    // }
}
