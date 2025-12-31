<?php

namespace App\Http\Resources;

use App\Models\Exchanges;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Http;

class ProductDetailTmapi extends JsonResource
{
    protected $amount;
    protected $percent;
    protected $provider;

    public function __construct($resource, $provider = 'taobao')
    {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->provider = $provider;
        $this->amount = Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;
        $this->percent = Exchanges::where('currency', 'product_percent')->first()->amount ?? 0;

        // Call the translation method
        $this->translateProductDetails();
    }

    public function toArray($request)
    {
        $quantityRanges = [];

        foreach (isset($this['tiered_price_info']['prices']) ? $this['tiered_price_info']['prices'] : [] as $range) {
            $quantityRanges[] = [
                'minQuantity' => $range['beginAmount'] ?? 1,
                'price' => round($this->addPercentToPrice($range['price'] ?? $this->resource['price_info']['price'], $this->amount, $this->percent)),
            ];
        }

        usort($quantityRanges, function ($a, $b) {
            return $a['minQuantity'] <=> $b['minQuantity'];
        });

        return [
            'id' => $this['item_id'],
            'title' => $this['title'],
            'price' => round($this->addPercentToPrice($this['price_info']['price'], $this->amount, $this->percent)),
            'discounted_price' => round($this->addPercentToPrice($this['price_info']['origin_price'] ?? 0, $this->amount, $this->percent)),
            'discount_percentage' => round(((floatval($this['price_info']['price']) - floatval($this['price_info']['origin_price'] ?? 0)) / 1) * 100),
            'quantityRanges' => $quantityRanges,
            'image' => preg_replace('/^\/\//', 'https://', $this['main_imgs'][0]),
            'quantity' => $this['sale_count'] ?? 0,
            'seller_info' => $this['shop_info'] ?? [],
            'delivery_info' => $this['delivery_info'] ?? [],
            'provider' => $this->provider,
            'description' => str_replace(["\n", "\\"], '', $this['desc'] ?? ''),
            'pictures' => $this->transformPictures($this['main_imgs']),
            'location' => $this['location'] ?? 'China',
            'product_url' => $this['product_url'],
            'videos' => $this['video_url'],
            'sku_price_range' => $this['sku_price_range'] ?? [],
            'skus' => $this->transformSkus($this['skus'] ?? null),
            'prop_imgs' => [],
            'props_list' => $this['sku_props'],
            'composition' => $this['product_props'],
            'is_liked' => false,
        ];
    }

    private function addPercentToPrice($priceInYuan, $amount, $percent = 0)
    {
        $priceInSum = floatval($priceInYuan) * floatval($amount);
        $additionalAmount = $priceInSum * ($percent / 100);
        return $priceInSum + $additionalAmount;
    }

    private function transformProductProps($props)
    {
        $composition = [];

        if (!is_array($props)) {
            return [];
        }

        foreach ($props as $key => $value) {
            // Handle grouped properties (Value is array/object)
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $composition[] = [
                        'key' => $subKey,
                        'value' => is_array($subValue) ? json_encode($subValue) : (string)$subValue,
                    ];
                }
            } else {
                // Handle flat properties (Value is string/number)
                // BUT: logic above (original) assumed nested due to double loop.
                // If the original loop was "foreach ($props as $prop) { foreach($prop...)}"
                // It ignored the top-level keys.
                // If we want to support both, we check type of $value.
                $composition[] = [
                    'key' => $key,
                    'value' => (string)$value,
                ];
            }
        }

        return $composition;
    }

    private function translateProductDetails()
    {
        // First maximize structural consistency
        $formattedProps = $this->transformProductProps($this['product_props'] ?? []);

        $data = [
            'from' => 'auto',
            'to' => request()->input('lang', 'uz'),
            'protected_paths' => ["extra.last_comment.author"],
            'common_protected_paths' => ["image"],
            'json' => [$this['title'], $formattedProps],
        ];

        try {
            // Make the translation API call
            $translateUrl = "https://aibit-translator.p.rapidapi.com/api/v1/translator/json";
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'aibit-translator.p.rapidapi.com',
                'x-rapidapi-key' => '74e939e778mshea2f00b1b0170dap1cc6eajsnf05e5c9be03a',
            ])->post($translateUrl, $data);

            if ($response->successful() && isset($response['trans'])) {
                $this['title'] = $response['trans'][0];
                $this['product_props'] = $response['trans'][1];
                return;
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        // Fallback: If translation failed, ensure we at least use the formatted props
        // so the frontend receives the correct structure (Array of {key, value})
        $this['product_props'] = $formattedProps;
    }
}
