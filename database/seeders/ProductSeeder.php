<?php

namespace Database\Seeders;

use App\Models\Exchanges;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductPicture;
use App\Models\ProductVideo;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\ProductComposition;
use Illuminate\Support\Facades\Http;

// /**
//  * Run the database seeds.
//  */
// public function run(): void
// {
//     Product::factory()
//         ->count(100)
//         ->has(ProductPicture::factory()->count(rand(0, 10)), 'pictures') // 3 pictures per product
//         ->has(ProductVideo::factory()->count(1), 'videos') // 2 videos per product
//         ->has(ProductColor::factory()->count(rand(1, 14)), 'colors') // 4 colors per product
//         ->has(ProductSize::factory()->count(5), 'sizes') // 5 sizes per product
//         ->has(ProductComposition::factory()->count(3), 'compositions') // 3 compositions per product
//         ->create();
// }
class ProductSeeder extends Seeder
{

    protected $apiKey = '06e38b86-e4a7-451e-91b3-2b40d2dd3e24';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $counter = 1;
        $amount = Exchanges::where('currency', 'yuan')->first()->amount ?? 1800;

        $url = "http://otapi.net/service-json/BatchGetItemFullInfo?instanceKey={$this->apiKey}&language=uz&signature=a67f4af4c43ea96f880740611d680127cbdf84ddbdaab674d8c3f265df1353e0&timestamp=20240526085023&sessionId=&itemParameters=&itemId=808807124248&blockList=";

        $response = Http::get($url);
        $products = $response->json();
        $productsList = $products['Result']['Item'];

        $originalPrice = isset($productsList['Price']['ConvertedPriceWithoutSign'])
            ? round($productsList['Price']['ConvertedPriceWithoutSign']) * $amount
            : null;

        $discountedPrice = isset($productsList['Promotions'])
            ? round($productsList['Promotions'][0]['Price']['ConvertedPriceList']['Internal']['Price']) * $amount
            : null;

        $discountPercentage = $originalPrice && $discountedPrice
            ? 100 - round((($discountedPrice / $originalPrice) * 100))
            : null;

        $product = Product::create([
            'product_category_id' => 16,
            'id' => $productsList['Id'] ?? null,
            'title' => $productsList['Title'] ?? null,
            'price' => $originalPrice,
            'discounted_price' => $discountedPrice,
            'discount_percent' => $discountPercentage,
            'image' => $productsList['MainPictureUrl'] ?? null,
            'quantity' => $productsList['MasterQuantity'] ?? null,
            'description' => $productsList['Description'] ?? null,
            'location' => $productsList['Location']['State'] ?? 'China',
            'product_url' => $productsList['ExternalItemUrl'] ?? null,
        ]);

        if (!empty($productsList['Pictures'])) {
            foreach ($productsList['Pictures'] as $picture) {
                ProductPicture::create([
                    'product_id' => $product->id,
                    'image_url' => $picture['Url'],
                    'is_main' => $picture['IsMain'] ?? false,
                ]);
            }
        }

        if (!empty($productsList['Videos'])) {
            foreach ($productsList['Videos'] as $video) {
                ProductVideo::create([
                    'product_id' => $product->id,
                    'url' => $video['Url'],
                    'preview_url' => $video['Url'],
                ]);
            }
        }

        if (!empty($productsList['Attributes'])) {
            $colors = array_values(array_filter(array_map(function ($attribute) {
                return [
                    'propertyName' => $attribute['PropertyName'] ?? null,
                    'value' => $attribute['Value'] ?? null,
                    'imageUrl' => $attribute['ImageUrl'] ?? null,
                    'miniImageUrl' => $attribute['MiniImageUrl'] ?? null,
                    'propertyId' => $attribute['Pid'] ?? null,
                    'valueId' => $attribute['Vid'] ?? null,
                ];
            }, $productsList['Attributes']), function ($color) {
                return ($color['propertyName'] === 'Rang bilan saralash' || $color['propertyName'] === 'tana rangi' || $color['propertyName'] === 'rang') && $color['imageUrl'] !== null;
            }));

            foreach ($colors as $color) {
                ProductColor::create([
                    'color_id' => $counter++,
                    'product_id' => $product->id,
                    'property_name' => $color['propertyName'],
                    'value' => $color['value'],
                    'image_url' => $color['imageUrl'],
                    'mini_image_url' => $color['miniImageUrl'],
                ]);
            }

            $sizes = array_values(array_filter(array_map(function ($attribute) {
                return [
                    'propertyName' => $attribute['PropertyName'] ?? null,
                    'value' => $attribute['Value'] ?? null,
                ];
            }, $productsList['Attributes']), function ($size) {
                return $size['propertyName'] === "o'lcham" || $size['propertyName'] === 'saqlash' || $size['propertyName'] === "Ma'lumot balandligi";
            }));

            foreach ($sizes as $size) {
                ProductSize::create([
                    'size_id' => $counter++,
                    'product_id' => $product->id,
                    'property_name' => $size['propertyName'],
                    'value' => $size['value'],
                ]);
            }

            $compositions = array_values(array_filter(array_map(function ($attribute) {
                return [
                    'propertyName' => $attribute['PropertyName'] ?? null,
                    'value' => $attribute['Value'] ?? null,
                ];
            }, $productsList['Attributes']), function ($composition) {
                return $composition['propertyName'] === "Moddiy tarkibiy qism";
            }));

            foreach ($compositions as $composition) {
                ProductComposition::create([
                    'product_id' => $product->id,
                    'property_name' => $composition['propertyName'],
                    'value' => $composition['value'],
                ]);
            }
        }
    }
}
