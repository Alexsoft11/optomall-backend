<?php

namespace Database\Seeders;

use App\Models\Exchanges;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Product;

class ProductsSeeder extends Seeder
{
    protected $apiKey = '06e38b86-e4a7-451e-91b3-2b40d2dd3e24';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amount = Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;




        $urls = [
            "http://otapi.net/service-json/BatchSearchItemsFrame?instanceKey={$this->apiKey}&language=uz&signature=e2082ad9248214795914d5f72e7a8ee835428149d23223eb00d97a67c2f9d1f6&timestamp=20240613130232&sessionId=&xmlParameters=%3CSearchItemsParameters%3E%0D%0A%0D%0A%3CCategoryId%3E121392004%3C%2FCategoryId%3E%0D%0A%3C%2FSearchItemsParameters%3E&framePosition=0&frameSize=50&blockList=",
            "http://otapi.net/service-json/BatchSearchItemsFrame?instanceKey={$this->apiKey}&language=uz&signature=e2082ad9248214795914d5f72e7a8ee835428149d23223eb00d97a67c2f9d1f6&timestamp=20240613130232&sessionId=&xmlParameters=%3CSearchItemsParameters%3E%0D%0A%0D%0A%3CCategoryId%3E50000852%3C%2FCategoryId%3E%0D%0A%3C%2FSearchItemsParameters%3E&framePosition=0&frameSize=100&blockList=",
            "http://otapi.net/service-json/BatchSearchItemsFrame?instanceKey={$this->apiKey}&language=uz&signature=e2082ad9248214795914d5f72e7a8ee835428149d23223eb00d97a67c2f9d1f6&timestamp=20240613130232&sessionId=&xmlParameters=%3CSearchItemsParameters%3E%0D%0A%0D%0A%3CCategoryId%3E50013189%3C%2FCategoryId%3E%0D%0A%3C%2FSearchItemsParameters%3E&framePosition=0&frameSize=100&blockList=",
        ];

        $mergedProducts = [];

        foreach ($urls as $url) {
            $response = Http::get($url);
            $products = $response->json();
            $productsList = $products['Result']['Items']['Items']['Content'];
            $mergedProducts = array_merge($mergedProducts, $productsList);
        }

        shuffle($mergedProducts);

        foreach ($mergedProducts as $productData) {
            $originalPrice = isset($productData['Price']['ConvertedPriceWithoutSign'])
                ? round($productData['Price']['ConvertedPriceWithoutSign']) * $amount
                : null;

            $discountedPrice = isset($productData['Promotions'])
                ? round($productData['Promotions'][0]['Price']['ConvertedPriceList']['Internal']['Price']) * $amount
                : null;

            $discountPercentage = $originalPrice && $discountedPrice
                ? 100 - round((($discountedPrice / $originalPrice) * 100))
                : null;

            Product::create([
                'product_category_id' => 16,
                'id' => $productData['Id'] ?? null,
                'title' => $productData['Title'] ?? null,
                'price' => $originalPrice,
                'discounted_price' => $discountedPrice,
                'discount_percent' => $discountPercentage,
                'image' => $productData['MainPictureUrl'] ?? null,
                'quantity' => $productData['MasterQuantity'] ?? null,
                'description' => $productData['Description'] ?? null,
                'location' => $productData['Location']['State'] ?? 'China',
                'product_url' => $productData['ExternalItemUrl'] ?? null,
            ]);
        }
    }
}
