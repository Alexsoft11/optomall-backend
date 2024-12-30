<?php

namespace App\Http\Controllers\Api\Products;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductDetail;
use App\Http\Resources\ProductDetailDatabase;
use App\Http\Resources\ProductDetailDatabaseToken;
use App\Http\Resources\ProductDetailOtapi;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductDetailTmapi;
use App\Http\Resources\ProductDetailTokenResource;
use OpenApi\Annotations as OA;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductTokenResource;
use App\Http\Resources\ScoreResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\BannerProduct;
use App\Models\Category;
use App\Models\HomePageProduct;
use App\Models\LikedProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TaobaoCategory;
use App\Models\TaobaoSubcategory;
use App\Models\TaobaoSubcategoryName;
use App\Traits\ApiResponseHelpers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponseHelpers;

    /**
     * @OA\Post(
     *     path="/api/translate",
     *     summary="Translate values in props_list",
     *     description="Translate the values inside the props_list array from one language to another using Deep Translate API.",
     *     tags={"Translation"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={
     *                     "0:0": ":黑色",
     *                     "0:1": ":红色",
     *                     "1:0": "尺码:S",
     *                     "1:1": "尺码:M"
     *                 }
     *             ),
     *             @OA\Property(property="from", type="string", example="auto"),
     *             @OA\Property(property="to", type="string", example="en")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translated props_list",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="translated_props_list",
     *                 type="object",
     *                 example={
     *                     "0:0": ":Black",
     *                     "0:1": ":Red",
     *                     "1:0": "Size:S",
     *                     "1:1": "Size:M"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function translate(Request $request)
    {
        // Get the props_list from the request
        $list = $request->input('data');
        $from = $request->input('from');
        $to = $request->input('to');
        $translatedList = [];

        // Prepare the data for the new API
        $data = [
            'from' => $from,
            'to' => $to,
            'protected_paths' => ["extra.last_comment.author"],
            'common_protected_paths' => ["image"],
            'json' => [$list],
        ];

        // Make the API call
        $url = "https://aibit-translator.p.rapidapi.com/api/v1/translator/json";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-rapidapi-host' => 'aibit-translator.p.rapidapi.com',
            'x-rapidapi-key' => '74e939e778mshea2f00b1b0170dap1cc6eajsnf05e5c9be03a',
        ])->post($url, $data);

        if ($response->successful()) {
            // Extract translated props_list from the response
            $translatedList = $response['trans'];
        } else {
            // Handle error
            \Log::error("Translation failed: " . $response->body());
        }

        // Return the updated props_list with translated values
        return response()->json(['translated' => $translatedList]);
    }

    /**
     * @OA\Get(
     *     path="/api/extract-id",
     *     tags={"Extract id"},
     *     summary="Extract 10-14 character IDs from URL",
     *     description="Extract IDs that are exactly 10 to 14 digits from the given URL passed as a query parameter",
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="The URL to extract the ID from",
     *         required=true,
     *         @OA\Schema(type="string", example="https://detail.1688.com/offer/624044966215.html")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful extraction",
     *         @OA\JsonContent(
     *             @OA\Property(property="ids", type="array", @OA\Items(type="string", example="624044966215"))
     *         )
     *     ),
     * )
     */
    public function extractId(Request $request)
    {
        $url = $request->query('url');

        preg_match_all('/\b\d{10,14}\b/', $url, $matches);
        $id = $matches[0][0] ?? null;

        $provider = null;
        if (strpos($url, '1688') !== false) {
            $provider = '1688';
        } elseif (strpos($url, 'taobao') !== false) {
            $provider = 'taobao';
        }

        return response()->json([
            'id' => $id,
            'provider' => $provider,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/liked-product",
     *     tags={"Liked products"},
     *     summary="Add or remove liked products",
     *     description="Add products to the liked products list or remove them if they already exist",
     *     operationId="addOrRemoveLikedProducts",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Liked products details",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object",
     *                 @OA\Property(property="product_id", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="provider", type="string"),
     *                 @OA\Property(property="image", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Liked products successfully added or removed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Liked products successfully added or removed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid data"),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="error", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function addLikedProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.product_id' => 'required|string',
            '*.title' => 'required|string',
            '*.price' => 'required|numeric',
            '*.provider' => 'required|string',
            '*.image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors(), 'error' => true], 400);
        }

        $userId = auth()->user()->id;
        $messages = [];

        foreach ($request->all() as $productData) {
            $productId = $productData['product_id'];

            $likedProduct = LikedProduct::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($likedProduct) {
                $likedProduct->delete();
                $messages[] = "Product ID {$productId} successfully removed";
            } else {
                LikedProduct::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'title' => $productData['title'],
                    'price' => $productData['price'],
                    'provider' => $productData['provider'],
                    'image' => $productData['image'],
                ]);
                $messages[] = "Product ID {$productId} successfully added";
            }
        }

        return response()->json(['message' => 'Liked products successfully added or removed'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/liked-products",
     *     tags={"Liked products"},
     *     summary="Get liked products",
     *     description="Retrieve the list of liked products for the authenticated user",
     *     operationId="getLikedProducts",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"},
     *             example="en"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of liked products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="string", example="1"),
     *                 @OA\Property(property="title", type="object",
     *                     @OA\Property(property="uz", type="string", example="Mahsulot nomi"),
     *                     @OA\Property(property="en", type="string", example="Product name"),
     *                     @OA\Property(property="ru", type="string", example="Название продукта")
     *                 ),
     *                 @OA\Property(property="price", type="number", format="float", example=29.99),
     *                 @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *                 @OA\Property(property="is_liked", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-07-10T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-07-10T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function getLikedProducts(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $lang = $request->query('lang', 'en'); // Default to 'en' if not provided

        $likedProducts = LikedProduct::where('user_id', $user->id)->get();

        $likedProductsArray = $likedProducts->map(function ($product) use ($lang) {
            return [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'title' => $product->title, // You can adjust this based on the language if needed
                'price' => $product->price,
                'image' => $product->image,
                'provider' => $product->provider,
                'is_liked' => true,
            ];
        });

        return response()->json($likedProductsArray, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/liked-products-id",
     *     tags={"Liked products"},
     *     summary="Get liked products id",
     *     description="Retrieve the list of liked products for the authenticated user",
     *     operationId="getLikedProductsId",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function getLikedProductsId(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Retrieve the liked products for the authenticated user
        $likedProducts = LikedProduct::where('user_id', $user->id)->pluck('product_id');

        // Return the list of product IDs as a JSON response
        return response()->json($likedProducts, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/categories-from-taobao",
     *     summary="Get categories from Taobao",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"en", "uz", "ru"},
     *             default="en"
     *         ),
     *         description="Language for category names"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories from Taobao",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(
     *                         property="subcategories",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(
     *                                 property="items",
     *                                 type="array",
     *                                 @OA\Items(type="string")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function categoriesFromTaoBao(Request $request)
    {
        $categories = TaobaoCategory::where('is_active', true)->get();

        return $this->respondWithSuccess([
            'categories' => CategoryResource::collection($categories)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products-from-taobao",
     *     summary="Get products from taobao",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="subCategory",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the subcategory"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="productsCount",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="The count of products"
     *     ),
     *  @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Products from taobao"),
     * )
     */
    public function productsFromTaoBao(Request $request)
    {
        $subCategory = $request->subCategory;
        $lang = $request->lang ?? 'en';
        $page = $request->page ? $request->page : 1;
        $productsCount = $request->productsCount ? $request->productsCount : 20;
        $url = "https://api.kongcrdv.com/taobao/api_call.php?api_name=item_search&lang=$lang&q=$subCategory&page=$page&page_size=$productsCount&key=kingdev5809@gmail.com";

        $response = Http::get($url);
        $products = $response->json();

        return $this->respondWithSuccess([
            'page' => $page,
            'page_size' => $productsCount,
            'products' => ProductResource::collection($products['items']['item'])
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products-from-taobao-token",
     *     summary="Get products from taobao",
     *     tags={"Products"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="subCategory",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the subcategory"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="productsCount",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="The count of products"
     *     ),
     *  @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Products from taobao"),
     * )
     */
    public function productsFromTaoBaoToken(Request $request)
    {
        $subCategory = $request->subCategory;
        $lang = $request->lang ?? 'en';
        $page = $request->page ? $request->page : 1;
        $productsCount = $request->productsCount ? $request->productsCount : 20;
        $url = "https://api.kongcrdv.com/taobao/api_call.php?api_name=item_search&lang=$lang&q=$subCategory&page=$page&page_size=$productsCount&key=kingdev5809@gmail.com";

        $response = Http::get($url);
        $products = $response->json();

        return $this->respondWithSuccess([
            'page' => $page,
            'page_size' => $productsCount,
            'products' => ProductTokenResource::collection($products['items']['item'])
        ]);
    }
    /**
     * @OA\Get(
     *     path="/api/product-detail",
     *     summary="Get product detail from taobao",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the product to retrieve details for"
     *     ),
     *  @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *    @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Product detail from taobao"),
     * )
     */
    public function productDetail(Request $request)
    {
        $productId = $request->productId;
        $lang = $request->lang ?? 'en';
        $provider = $request->provider ?? 'taobao';

        // Function to get product details
        $getProductDetails = function ($provider) use ($productId, $lang) {
            $url = "https://api.kongcrdv.com/$provider/api_call.php?api_name=item_get&lang=$lang&num_iid=$productId&key=kingdev5809@gmail.com";
            return Http::get($url)->json();
        };

        // If product not found, switch the provider

        // // Function to get random products
        // $getRandomProducts = function ($provider, $pic_url, $lang) {
        //     $imageSearchUrl = "https://kongcrdv.com/$provider/api/export_api.php?action=item_search_img&key=kingdev5809@gmail.com&imgid=$pic_url&lang=$lang";
        //     return Http::get($imageSearchUrl)->json();
        // };

        // // Try to get random products with the initial provider
        // $randomProducts = $getRandomProducts($provider, $productDetail['item']['pic_url'], $lang);

        // // If random products not found, switch the provider
        // if (!isset($randomProducts['data']['items']['item']) || empty($randomProducts['data']['items']['item'])) {
        //     $imageSearchProvider = $provider === 'taobao' ? '1688' : 'taobao';
        //     $randomProducts = $getRandomProducts($imageSearchProvider, $productDetail['item']['pic_url'], $lang);
        // }

        // Prepare recommended products
        // $recommended = ProductTokenResource::collection(collect($randomProducts['data']['items']['item'])->take(5));
        $productDatabase = Product::where('id', $productId)->first();

        if ($provider == 'local') {
            $product = new ProductDetailDatabase($productDatabase, $lang);
        } else {
            // Try to get product details with the initial provider
            $productDetail = $getProductDetails($provider);
            // return $productDetail;
            // If product not found, switch the provider
            if (!isset($productDetail['item'])) {
                // If still not found, return 404 response
                if (!isset($productDetail['item'])) {
                    return response()->json(['error' => 'product_not_found'], 404);
                }
            }
            $product = new ProductDetail($productDetail['item'], $provider);
        }

        return $this->respondWithSuccess([
            'detail' => $product,
            'score' => [
                'rating' => $productDetail['item']['seller_info']['item_score'] ?? 4.6,
                'sold' => $productDetail['item']['sales'] ?? 1000,
            ],
            // 'recommended' => $recommended,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/product-detail-otapi",
     *     summary="Get product detail",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the product to retrieve details for"
     *     ),
     *  @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *    @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Product detail from taobao"),
     * )
     */
    public function productDetailOtapi(Request $request)
    {
        $productId = $request->productId;
        $lang = $request->lang ?? 'en';
        $provider = $request->provider ?? 'taobao';
        $cacheKey = "product-detail-otapi?productId={$productId}&lang={$lang}";

        $productDetail = Cache::remember($cacheKey, 60 * 5, function () use ($productId, $lang) {
            $url = "https://otapi.net/service-json/BatchGetItemFullInfo?instanceKey=74a68481-54a6-4069-98c0-f19fecf1f58f&language=$lang&itemId=$productId&blockList=Description&blockList=RecommendedItems";

            try {
                return Http::timeout(50)->withoutVerifying()->get($url)->json(); // Increased timeout to 10 seconds
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return null; // Return null on timeout
            }
        });

        // Check if provider is 'local' to fetch product from local database
        if ($provider == 'local') {
            $productDatabase = Product::where('id', $productId)->first();

            // Handle the case where the product is not found in the local database
            if (!$productDatabase) {
                return response()->json(['error' => 'product_not_found'], 404);
            }

            $product = new ProductDetailDatabase($productDatabase, $lang);

            return $this->respondWithSuccess([
                'detail' => $product,
                'score' => [
                    'rating' => $productDatabase->seller_info['item_score'] ?? 4.6,
                    'sold' => $productDatabase->sales ?? 1000,
                ],
            ]);
        }

        // Check if product details were successfully retrieved or if an error occurred
        if (!$productDetail || !isset($productDetail['Result']['Item'])) {
            return response()->json(['error' => 'product_not_found', 'productDetail' => $productDetail], 404); // Handle timeout or product not found
        }

        $product = new ProductDetailOtapi($productDetail['Result']['Item'], $provider);
        $recommended = ProductResource::collection($productDetail['Result']['RecommendedItems']['Content'] ?? []);

        return $this->respondWithSuccess([
            'detail' => $product,
            'score' => [
                'rating' => $productDetail['Result']['Item']['seller_info']['item_score'] ?? 4.6,
                'sold' => $productDetail['Result']['Item']['sales'] ?? 1000,
            ],
            'recommended' => $recommended,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/product-detail-onbound",
     *     summary="Get product detail",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the product to retrieve details for"
     *     ),
     *  @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *    @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Product detail from taobao"),
     * )
     */
    public function productDetailOnbound(Request $request)
    {
        $productId = $request->productId;

        // Remove 'abb-' if present in the product ID
        if (strpos($productId, 'abb-') !== false) {
            $productId = str_replace('abb-', '', $productId);
        }

        $lang = 'zh-CN'; // Default language
        $provider = $request->input('provider', 'taobao'); // Default provider is 'taobao'

        // Construct the cache key for product details
        $cacheKey = "product-detail?productId={$productId}&lang={$lang}&provider={$provider}";

        // Build the URL to fetch product details
        $url = "https://api-gw.onebound.cn/$provider/item_get/?key=t3832825558&&num_iid=$productId&&cache=no&&secret=20240731&lang=$lang";

        // Fetch product details from cache or make an API call if not cached
        $productDetail = Cache::remember($cacheKey, 60 * 5, function () use ($url) {
            try {
                // Make the API call with a 50 second timeout
                return Http::timeout(50)->withoutVerifying()->get($url)->json();
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Return null on timeout or error
                return null;
            }
        });

        // Handle local provider case (if the provider is 'local')
        if ($provider == 'local') {
            $productDatabase = Product::where('id', $productId)->first();

            if (!$productDatabase) {
                return response()->json(['error' => 'product_not_found'], 404);
            }

            $product = new ProductDetailDatabase($productDatabase, $lang);

            return $this->respondWithSuccess([
                'detail' => $product,
                'score' => [
                    'rating' => $productDatabase->seller_info['item_score'] ?? 4.6,
                    'sold' => $productDatabase->sales ?? 1000,
                ],
            ]);
        }

        if ($productDetail['item']['format_check'] == 'fail') {
            return response()->json(['error' => 'product_not_found', 'productDetail' => $productDetail], 404);
        }

        // Construct product resource from the fetched details
        $product = new ProductDetailResource($productDetail['item'], $provider);

        // Prepare the data for translation
        $data = [
            'from' => 'auto',
            'to' => $request->lang,
            'protected_paths' => ["extra.last_comment.author"],
            'common_protected_paths' => ["image"],
            'json' => [$product['title'], $product['props']],
        ];

        // Make the translation API call
        $translateUrl = "https://aibit-translator.p.rapidapi.com/api/v1/translator/json";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-rapidapi-host' => 'aibit-translator.p.rapidapi.com',
            'x-rapidapi-key' => '74e939e778mshea2f00b1b0170dap1cc6eajsnf05e5c9be03a',
        ])->post($translateUrl, $data);

        // Update product details with the translated text
        $product['title'] = $response['trans'][0];
        $product['props'] = $response['trans'][1];

        // Return success response with product details and score
        return $this->respondWithSuccess([
            'url' => $url,
            'detail' => $product,
            'score' => [
                'rating' => $productDetail['item']['seller_info']['item_score'] ?? 4.6,
                'sold' => $productDetail['item']['sales'] ?? 1000,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/product-detail-tmapi",
     *     summary="Get product detail tmapi",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the product to retrieve details for"
     *     ),
     *  @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *    @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Product detail from taobao tmapi"),
     * )
     */
    public function productDetailTmapi(Request $request)
    {
        $productId = $request->productId;

        // Remove 'abb-' if present in the product ID
        if (strpos($productId, 'abb-') !== false) {
            $productId = str_replace('abb-', '', $productId);
        }

        $lang = $request->input('lang'); // Default language
        $provider = $request->input('provider', 'taobao'); // Default provider is 'taobao'

        // Construct the cache key for product details
        $cacheKey = "product-detail-tmapi?productId={$productId}&lang={$lang}&provider={$provider}";

        // Build the URL to fetch product details
        $url = $provider == 'taobao' ?
            "http://api.tmapi.top/taobao/item_detail?item_id=$productId&apiToken=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VybmFtZSI6Im9wdG9tYWxsIiwiQ29taWQiOm51bGwsIlJvbGVpZCI6bnVsbCwiaXNzIjoidG1hcGkiLCJzdWIiOiJvcHRvbWFsbCIsImF1ZCI6WyIiXSwiaWF0IjoxNzI3ODQzODA3fQ.MjSYALtr8x19ybMi8aJvRJk2Pl_hNwMpuNpWyAwx9AQ" :
            "http://api.tmapi.top/1688/v2/item_detail?item_id=$productId&apiToken=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VybmFtZSI6Im9wdG9tYWxsIiwiQ29taWQiOm51bGwsIlJvbGVpZCI6bnVsbCwiaXNzIjoidG1hcGkiLCJzdWIiOiJvcHRvbWFsbCIsImF1ZCI6WyIiXSwiaWF0IjoxNzI3ODQzODA3fQ.MjSYALtr8x19ybMi8aJvRJk2Pl_hNwMpuNpWyAwx9AQ";

        // Fetch product details from cache or make an API call if not cached
        $productDetail = Cache::remember($cacheKey, 60 * 5, function () use ($url) {
            try {
                // Make the API call with a 50 second timeout
                return Http::timeout(50)->withoutVerifying()->get($url)->json();
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Return null on timeout or error
                return null;
            }
        });

        // Handle local provider case (if the provider is 'local')
        if ($provider == 'local') {
            $productDatabase = Product::where('id', $productId)->first();

            if (!$productDatabase) {
                return response()->json(['error' => 'product_not_found'], 404);
            }

            $product = new ProductDetailDatabase($productDatabase, $lang);

            return $this->respondWithSuccess([
                'detail' => $product,
                'score' => [
                    'rating' => $productDatabase->seller_info['item_score'] ?? 4.6,
                    'sold' => $productDatabase->sales ?? 1000,
                ],
            ]);
        }

        // Check for failed format
        if ($productDetail['code'] !== 200) {
            return response()->json(['error' => 'product_not_found', 'productDetail' => $productDetail], 404);
        }

        // Make an asynchronous call to construct product resource
        $product = new ProductDetailTmapi($productDetail['data'], $provider);

        // Return success response with product details and score
        return $this->respondWithSuccess([
            'detail' => $product,
            'score' => [
                'rating' => $productDetail['item']['seller_info']['item_score'] ?? 4.6,
                'sold' => $productDetail['item']['sales'] ?? 1000,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/product-detail-desc",
     *     summary="Get product detail desc",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the product to retrieve details for"
     *     ),
     *      @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *    @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Product detail from taobao tmapi"),
     * )
     */
    public function productDetailDesc(Request $request)
    {
        $productId = $request->productId;
        $provider = $request->provider;

        // Remove 'abb-' if present in the product ID
        if (strpos($productId, 'abb-') !== false) {
            $productId = str_replace('abb-', '', $productId);
        }

        $lang = 'zh-CN'; // Default language

        // Build the URL to fetch product details
        $url = "http://api.tmapi.top/$provider/item_desc?item_id=$productId&apiToken=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VybmFtZSI6Im9wdG9tYWxsIiwiQ29taWQiOm51bGwsIlJvbGVpZCI6bnVsbCwiaXNzIjoidG1hcGkiLCJzdWIiOiJvcHRvbWFsbCIsImF1ZCI6WyIiXSwiaWF0IjoxNzI3ODQzODA3fQ.MjSYALtr8x19ybMi8aJvRJk2Pl_hNwMpuNpWyAwx9AQ";

        // Fetch product details from cache or make an API call if not cached
        $response = Http::timeout(50)->withoutVerifying()->get($url)->json();

        return $this->respondWithSuccess([
            'desc' => $response['data'],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/product-detail-from-taobao",
     *     summary="Get product detail from taobao",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the product to retrieve details for"
     *     ),
     *  @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *    @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Product detail from taobao"),
     * )
     */
    public function productDetailFromTaoBao(Request $request)
    {
        $productId = $request->productId;
        $lang = $request->lang ?? 'en';
        $provider = $request->provider ?? 'taobao';

        // Function to get product details
        $getProductDetails = function ($provider) use ($productId, $lang) {
            $url = "https://api.kongcrdv.com/$provider/api_call.php?api_name=item_get&lang=$lang&num_iid=$productId&key=kingdev5809@gmail.com";
            return Http::get($url)->json();
        };

        // Check if provider is 'local' to fetch product from local database
        if ($provider == 'local') {
            $productDatabase = Product::where('id', $productId)->first();

            // Handle the case where the product is not found in the local database
            if (!$productDatabase) {
                return response()->json(['error' => 'product_not_found'], 404);
            }

            $product = new ProductDetailDatabase($productDatabase, $lang);

            return $this->respondWithSuccess([
                'detail' => $product,
                'score' => [
                    'rating' => $productDatabase->seller_info['item_score'] ?? 4.6,
                    'sold' => $productDatabase->sales ?? 1000,
                ],
            ]);
        }

        // Try to get product details with the initial provider
        $productDetail = $getProductDetails($provider);

        // If product not found, switch the provider and try again
        if (!isset($productDetail['item'])) {
            $provider = $provider === 'taobao' ? '1688' : 'taobao';
            $productDetail = $getProductDetails($provider);

            // If still not found, return 404 response
            if (!isset($productDetail['item'])) {
                return response()->json(['error' => 'product_not_found'], 404);
            }
        }

        // At this point, $productDetail['item'] is guaranteed to be set
        $product = new ProductDetailResource($productDetail['item'], $provider);

        return $this->respondWithSuccess([
            'detail' => $product,
            'score' => [
                'rating' => $productDetail['item']['seller_info']['item_score'] ?? 4.6,
                'sold' => $productDetail['item']['sales'] ?? 1000,
            ],
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/product-detail-from-taobao-token",
     *     summary="Get product detail from taobao",
     *     tags={"Products"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="productId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the product to retrieve details for"
     *     ),
     * @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Product detail from taobao"),
     * )
     */

    public function productDetailFromTaoBaoToken(Request $request)
    {
        $productId = $request->productId;
        $lang = $request->lang ?? 'en';
        $provider = $request->provider ?? 'taobao';

        // Function to get product details
        $getProductDetails = function ($provider) use ($productId, $lang) {
            $url = "https://api.kongcrdv.com/$provider/api_call.php?api_name=item_get&lang=$lang&num_iid=$productId&key=kingdev5809@gmail.com";
            return Http::get($url)->json();
        };

        // Try to get product details with the initial provider
        $productDetail = $getProductDetails($provider);

        // If product not found, switch the provider
        if (!isset($productDetail['item'])) {
            $provider = $provider === 'taobao' ? '1688' : 'taobao';
            $productDetail = $getProductDetails($provider);

            // If still not found, return 404 response
            if (!isset($productDetail['item'])) {
                return response()->json(['error' => 'product_not_found'], 404);
            }
        }
        // // Function to get random products
        // $getRandomProducts = function ($provider, $pic_url, $lang) {
        //     $imageSearchUrl = "https://kongcrdv.com/$provider/api/export_api.php?action=item_search_img&key=kingdev5809@gmail.com&imgid=$pic_url&lang=$lang";
        //     return Http::get($imageSearchUrl)->json();
        // };

        // // Try to get random products with the initial provider
        // $randomProducts = $getRandomProducts($provider, $productDetail['item']['pic_url'], $lang);

        // // If random products not found, switch the provider
        // if (!isset($randomProducts['data']['items']['item']) || empty($randomProducts['data']['items']['item'])) {
        //     $imageSearchProvider = $provider === 'taobao' ? '1688' : 'taobao';
        //     $randomProducts = $getRandomProducts($imageSearchProvider, $productDetail['item']['pic_url'], $lang);
        // }

        // Prepare recommended products
        // $recommended = ProductTokenResource::collection(collect($randomProducts['data']['items']['item'])->take(5));
        $productDatabase = Product::where('id', $productId)->first();

        if ($provider == 'local') {
            $product = new ProductDetailDatabaseToken($productDatabase, $lang);
        } else {
            if (!isset($productDetail['item'])) {
                if (!isset($productDetail['item'])) {
                    return response()->json(['error' => 'product_not_found'], 404);
                }
            }
            $product = new ProductDetailTokenResource($productDetail['item']);
        }

        return $this->respondWithSuccess([
            'detail' => $product,
            'score' => [
                'rating' => $productDetail['item']['seller_info']['item_score'] ?? 4.6,
                'sold' => $productDetail['item']['sales'] ?? 1000,
            ],
            // 'recommended' => $recommended,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search-products-from-taobao",
     *     summary="Search products by title.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="value",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Search products by title."
     *     ),
     *     @OA\Parameter(
     *         name="productsCount",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="The count of products"
     *     ),
     *  @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *  @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * @OA\Parameter(
     *         name="imageUrl",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * @OA\Parameter(
     *         name="catId",
     *         in="query",
     *         required=false,
     *     ),
     *  @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *  
     *     ),
     *     @OA\Response(response="200", description="Products from taobao"),
     * )
     */
    public function searchProductsFromTaobao(Request $request)
    {
        // Cache the API response for 5 minutes to avoid repeated external API calls
        $cacheKey = 'search_products_' . md5(json_encode($request->all()));

        // Try to get the cached response
        $cachedResponse = Cache::get($cacheKey);
        if ($cachedResponse) {
            return $cachedResponse; // Return cached response if exists
        }

        // If cache is not available, make an API call
        try {
            $value = $request->input('value');
            $sort = $request->input('sort'); // Expecting format like 'VendorRating:Desc'
            $page = $request->input('page', 0);
            $productsCount = $request->input('productsCount', 20); // Reduced productsCount to 20 for quicker responses
            $provider = strtolower($request->input('provider', 'taobao'));
            $lang = $request->input('lang', 'en');
            $imageUrl = $request->input('imageUrl');

            // Fetch the subcategory based on catId
            $subcategory = TaobaoSubcategory::where('unique_id', $request->catId)
                ->pluck('unique')
                ->first() ?? TaobaoSubcategoryName::where('unique_id', $request->catId)
                ->pluck('unique')
                ->first();

            // Check if we need to translate the value before the API call
            $translatedValue = $subcategory ? $subcategory : $this->translateText($value); // Translate to Chinese for search

            // Base API URL construction
            $baseApiUrl = $imageUrl
                ? "https://api-gw.onebound.cn/$provider/item_search_img/?key=t3832825558&imgid=$imageUrl&seller_info=no&lang=$lang&page=$page&sort=$sort&page_size=$productsCount&secret=20240731"
                : "https://api-gw.onebound.cn/$provider/item_search/?key=t3832825558&q=" . $translatedValue . "&page=$page&sort=$sort&page_size=$productsCount&seller_info=no&lang=$lang&secret=20240731";

            // Making the external API request
            $response = Http::timeout(50)->withoutVerifying()->get($baseApiUrl);
            $products = $response->json();

            // Check if products are found
            if (empty($products['items']['item'])) {
                return response()->json([
                    'error' => 'product_not_found',
                    'details' => $products,
                ], 404);
            }

            // Cache the successful response
            Cache::put($cacheKey, $this->respondWithSuccess([
                'products' => collect($products['items']['item'])->map(function ($product) use ($provider, $lang) {
                    return (new ProductResource($product, $provider, $lang))->resolve();
                }),
            ]), 600); // Store in cache for 10 minutes

            return $this->respondWithSuccess([
                'products' => collect($products['items']['item'])->map(function ($product) use ($provider, $lang) {
                    return (new ProductResource($product, $provider, $lang))->resolve();
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching products from Taobao: ' . $e->getMessage());
            return response()->json(['error' => 'service_unavailable', 'message' => $e->getMessage()], 503);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/search-products-from-tmapi",
     *     summary="Search products by title.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="value",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Search products by title."
     *     ),
     *     @OA\Parameter(
     *         name="productsCount",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="The count of products"
     *     ),
     *  @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *  @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * @OA\Parameter(
     *         name="imageUrl",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * @OA\Parameter(
     *         name="catId",
     *         in="query",
     *         required=false,
     *     ),
     *  @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *  
     *     ),
     *     @OA\Response(response="200", description="Products from taobao"),
     * )
     */
    public function searchProductsTmapi(Request $request)
    {
        $cacheKey = $this->generateCacheKey($request);

        // Check cache
        $cachedResponse = Cache::get($cacheKey);
        if ($cachedResponse) {
            return $cachedResponse;
        }

        try {
            $params = $this->prepareSearchParams($request);
            $translatedValue = $this->getTranslatedValue($params['value'], $params['catId']);
            $baseApiUrl = $this->constructApiUrl($params, $translatedValue);

            $response = Http::withoutVerifying()->get($baseApiUrl);
            $products = $response->json();

            if (empty($products['data']['items']) && empty($products['items']['item'])) {
                return response()->json(['error' => 'product_not_found'], 404);
            }

            $mappedProducts = $this->mapProducts($products, $params['provider'], $params['lang']);
            Cache::put($cacheKey, $mappedProducts, 600); // Cache for 10 minutes

            // return $mappedProducts;
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return response()->json(['error' => 'service_unavailable', 'message' => $e->getMessage()], 503);
        }
    }

    private function generateCacheKey(Request $request)
    {
        $params = $request->only(['value', 'lang', 'provider', 'catId', 'sort', 'imageUrl', 'page', 'productsCount']);
        return 'search_products_tmapi_' . md5(json_encode($params));
    }

    private function prepareSearchParams(Request $request)
    {
        return [
            'value' => $request->input('value'),
            'sort' => $request->input('sort', 'default'),
            'page' => $request->input('page', 1),
            'productsCount' => $request->input('productsCount', 20),
            'provider' => strtolower($request->input('provider', 'taobao')),
            'lang' => $request->input('lang', 'en'),
            'imageUrl' => $request->input('imageUrl'),
            'catId' => $request->input('catId'),
        ];
    }

    private function getTranslatedValue($value, $catId)
    {
        if ($catId) {
            return TaobaoSubcategory::where('unique_id', $catId)->value('unique')
                ?? TaobaoSubcategoryName::where('unique_id', $catId)->value('unique');
        }

        // Cache translation results
        $cacheKey = 'translated_value_' . md5($value);
        return Cache::remember($cacheKey, 600, function () use ($value) {
            return $this->translateText($value);
        });
    }

    private function constructApiUrl($params, $translatedValue)
    {
        $baseUrls = [
            '1688' => $params['imageUrl']
                ? "http://api.tmapi.top/1688/en/search/image?img_url={$params['imageUrl']}&page={$params['page']}&page_size={$params['productsCount']}&sort={$params['sort']}&apiToken=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VybmFtZSI6Im9wdG9tYWxsIiwiQ29taWQiOm51bGwsIlJvbGVpZCI6bnVsbCwiaXNzIjoidG1hcGkiLCJzdWIiOiJvcHRvbWFsbCIsImF1ZCI6WyIiXSwiaWF0IjoxNzI3ODQzODA3fQ.MjSYALtr8x19ybMi8aJvRJk2Pl_hNwMpuNpWyAwx9AQ"
                : "http://api.tmapi.top/1688/search/items?keyword=$translatedValue&page={$params['page']}&page_size={$params['productsCount']}&sort={$params['sort']}&apiToken=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VybmFtZSI6Im9wdG9tYWxsIiwiQ29taWQiOm51bGwsIlJvbGVpZCI6bnVsbCwiaXNzIjoidG1hcGkiLCJzdWIiOiJvcHRvbWFsbCIsImF1ZCI6WyIiXSwiaWF0IjoxNzI3ODQzODA3fQ.MjSYALtr8x19ybMi8aJvRJk2Pl_hNwMpuNpWyAwx9AQ",
            'taobao' => $params['imageUrl']
                ? "https://api-gw.onebound.cn/taobao/item_search_img/?key=t3832825558&imgid={$params['imageUrl']}&seller_info=no&lang={$params['lang']}&page={$params['page']}&sort={$params['sort']}&page_size={$params['productsCount']}&secret=20240731"
                : "https://api-gw.onebound.cn/taobao/item_search/?key=t3832825558&q=$translatedValue&page={$params['page']}&sort={$params['sort']}&page_size={$params['productsCount']}&seller_info=no&lang={$params['lang']}&secret=20240731",
        ];

        return $baseUrls[$params['provider']] ?? $baseUrls['taobao'];
    }

    private function mapProducts($products, $provider, $lang)
    {
        $items = $provider === '1688' ? $products['data']['items'] : $products['items']['item'];
        return [
            'products' => collect($items)->map(fn($product) => (new ProductResource($product, $provider, $lang))->resolve()),
        ];
    }

    private function translateText($text)
    {
        $data = [
            'from' => 'auto',
            'to' => 'zh-CN',
            'text' => $text
        ];

        // Make the translation API call
        $translateUrl = "https://aibit-translator.p.rapidapi.com/api/v1/translator/text";
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'aibit-translator.p.rapidapi.com',
                'x-rapidapi-key' => '74e939e778mshea2f00b1b0170dap1cc6eajsnf05e5c9be03a',
            ])->post($translateUrl, $data);

            $result = $response->json();
            // return $result;
            // Return the translated text or original if not available
            return $result['trans'] ?? $text;
        } catch (\Exception $e) {
            Log::error('Translation API error: ' . $e->getMessage());
            return $text; // Return original text if translation fails
        }
    }

    /**
     * @OA\Get(
     *     path="/api/search-products-from-taobao-token",
     *     summary="Search products by title.",
     *     tags={"Products"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="value",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Search products by title."
     *     ),
     *     @OA\Parameter(
     *         name="productsCount",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="The count of products"
     *     ),
     *  @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *  @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *  @OA\Parameter(
     *         name="startPrice",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *  @OA\Parameter(
     *         name="endPrice",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *  @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"uz", "ru", "en"}
     *         ),
     *         description="Language code for the product details. Available options: uz, ru, en"
     *     ),
     *     @OA\Response(response="200", description="Products from taobao"),
     * )
     */
    public function searchProductsFromTaobaoToken(Request $request)
    {
        $value = $request->value;
        $sort = $request->sort;
        $page = $request->page ?? 1;
        $productsCount = $request->productsCount ?? 20;
        $provider = $request->provider ?? 'taobao';
        $lang = $request->lang ?? 'en';
        $imageUrl = $request->imageUrl;
        $apiKey = 'kingdev5809@gmail.com';
        $subcategory = TaobaoSubcategory::where('unique_id', $request->catId)->pluck('unique')->first() ?? TaobaoSubcategoryName::where('unique_id', $request->catId)->pluck('unique')->first();

        // Build the API URL
        $apiUrl = $imageUrl
            ? "https://kongcrdv.com/$provider/api/export_api.php?action=item_search_img&key=$apiKey&imgid=$imageUrl&lang=$lang&page=$page&page_size=$productsCount&sort=$sort"
            : "https://kongcrdv.com/$provider/api/export_api.php?action=item_search&lang=$lang&key=$apiKey&q=" . ($value ? $value : $subcategory) . "&page=$page&sort=";
        // return $apiUrl;
        // Make the API call
        $response = Http::get($apiUrl);
        $products = $response->json();
        // Extract the product data
        if (empty($products['data']['items']['item'])) {
            return response()->json(['error' => 'product_not_found', $products], 404);
        }
        $productData = $products['data']['items']['item'];
        // return $productData;

        // Return the final response
        return $this->respondWithSuccess([
            'products' => ProductResource::collection($productData)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/image-search",
     *     summary="Search for products based on the uploaded image",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="The image to upload and search for"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No items found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="No items found",
     *                 description="Error message"
     *             )
     *         )
     *     )
     * )
     */
    public function imageSearch(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:10240', // 10240 KB = 10 MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'messages' => 'image_file_max_size_exceeded',
            ], 400);
        }

        // Store the uploaded image
        $imagePath = $request->file('image')->store('images', 'public');
        $imageFullPath = url('storage/' . $imagePath);

        $apiUrl = 'http://api.tmapi.top/1688/tools/image/convert_url?apiToken=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VybmFtZSI6Im9wdG9tYWxsIiwiQ29taWQiOm51bGwsIlJvbGVpZCI6bnVsbCwiaXNzIjoidG1hcGkiLCJzdWIiOiJvcHRvbWFsbCIsImF1ZCI6WyIiXSwiaWF0IjoxNzI3ODQzODA3fQ.MjSYALtr8x19ybMi8aJvRJk2Pl_hNwMpuNpWyAwx9AQ';
        $response = Http::post($apiUrl, [
            'url' => $imageFullPath,
        ]);

        // // Convert the image to Base64
        // $imageData = file_get_contents($imageFullPath);
        // $base64Image = base64_encode($imageData);

        // // Build the API URL with query parameters

        // // Send POST request to the API with Base64 image in the body

        // // Decode the response from the API
        // $responseData = $response->json();
        // // Check if the API call was successful
        // if (isset($responseData['items']['item']['name'])) {
        //     return response()->json([
        //         'url' => $responseData['items']['item']['name'],  // Adjust according to the actual API response structure
        //     ]);
        // } else {
        //     return response()->json([
        //         'error' => true,
        //         'messages' => 'external_api_error',
        //         'details' => $responseData,  // Return error details from the API
        //     ], 500);
        // }

        return response()->json(['url' => $response['data']['image_url'], 'urlOrg' => $imageFullPath]);
    }


    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Products",
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Top or Popular",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="productsCount",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="The count of products"
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     tags={"Products"},
     *     @OA\Response(
     *         response="200",
     *         description="Products from taobao",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="price", type="number", format="float"),
     *                     @OA\Property(property="discounted_price", type="number", format="float"),
     *                     @OA\Property(property="product_url", type="string"),
     *                     @OA\Property(property="provider", type="string"),
     *                     @OA\Property(property="is_liked", type="boolean")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function products(Request $request)
    {
        // Retrieve the query parameters
        $query = $request->input('query');
        $lang = $request->input('lang') ?? 'en';
        $page = $request->input('page') ?? 1; // Default page number is 1
        $productsCount = $request->input('productsCount') ?? 60; // Default to 40 products per page

        // Ensure productsCount is an integer and not less than 1
        $productsCount = max(1, (int)$productsCount);

        // Retrieve the products based on the query type
        $productsQuery = HomePageProduct::where('type', $query)->inRandomOrder();

        // Apply pagination
        $products = $productsQuery->paginate($productsCount, ['*'], 'page', $page);

        // Transform the products into the desired structure
        $transformedProducts = $products->getCollection()->map(function ($product) use ($lang) {
            $isLiked = auth()->check() && auth()->user()->likedProducts()->where('product_id', $product->product_id)->exists();
            return [
                'id' => $product->product_id,
                'title' => $product['title_' . $lang],
                'image' => $product->image,
                'price' => $product->price,
                'discounted_price' => $product->discounted_price,
                'product_url' => $product->product_url,
                'provider' => $product->provider,
                'is_liked' => $isLiked,
            ];
        });

        // Return the transformed products with a success response and pagination metadata
        return $this->respondWithSuccess([
            'products' => $transformedProducts,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }



    /**
     * @OA\Get(
     *     path="/api/products-token",
     *     summary="Products",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Top or Popular",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"热销", "推荐"}
     *         )
     *     ),
     *  @OA\Parameter(
     *         name="productsCount",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="The count of products"
     *     ),
     * @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *     ),
     *  @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     tags={"Products"},
     *     @OA\Response(response="200", description="Products from taobao"),
     * )
     */

    public function productsToken(Request $request)
    {
        // Retrieve the query parameters
        $query = $request->input('query');
        $lang = $request->input('lang');
        $page = $request->input('page');
        $productsCount = $request->input('productsCount') ?? 10;

        // Define the URLs based on the query parameter
        if ($query === "discount") {
            $urls = [
                "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=shirt&lang=$lang",
                "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=suits&lang=$lang",
                // "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=shoes&lang=$lang"
            ];
        } elseif ($query === "top") {
            $urls = [
                "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=trainers&lang=$lang",
                "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=jeans&lang=$lang",
                // "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=dresses&lang=$lang"
            ];
        } elseif ($query === "recomend") {
            $urls = [
                "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=school&lang=$lang",
                "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=smartphones&lang=$lang",
                // "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=cases&lang=$lang"
            ];
        } else {
            $urls = [];
        }

        // Make asynchronous requests to all URLs
        $responses = collect($urls)->map(fn($url) => Http::get($url));

        // Merge the results into one array, handle potential errors
        $products = $responses->reduce(function ($carry, $response) {
            if ($response->successful()) {
                $items = $response->json()['data']['items']['item'] ?? [];
                return array_merge($carry, $items);
            }
            return $carry;
        }, []);

        return $this->respondWithSuccess([
            'products' => ProductDetailResource::collection($products),
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/additional-categories",
     *     summary="Get list of active categories with products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         description="Language code (uz, ru, en)",
     *         @OA\Schema(type="string", enum={"uz", "ru", "en"}, example="uz")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active categories with products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="categories", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="name", type="object",
     *                         @OA\Property(property="uz", type="string"),
     *                         @OA\Property(property="ru", type="string"),
     *                         @OA\Property(property="en", type="string")
     *                     ),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="image_mobile", type="string"),
     *                     @OA\Property(property="is_active", type="boolean"),
     *                     @OA\Property(property="products", type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="title", type="object",
     *                                 @OA\Property(property="uz", type="string"),
     *                                 @OA\Property(property="ru", type="string"),
     *                                 @OA\Property(property="en", type="string")
     *                             ),
     *                             @OA\Property(property="price", type="string"),
     *                             @OA\Property(property="description", type="object",
     *                                 @OA\Property(property="uz", type="string"),
     *                                 @OA\Property(property="ru", type="string"),
     *                                 @OA\Property(property="en", type="string")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function categories(Request $request)
    {
        $limit = $request->query('limit', 3);
        $lang = $request->query('lang', 'uz'); // Default lang is 'uz'
        $categories = Category::where('is_active', true)
            ->with(['bannerProducts' => function ($query) use ($limit, $lang) {
                $query->limit($limit)
                    ->select(
                        'id',
                        'product_id',
                        'category_id',
                        'title_uz',
                        'title_ru',
                        'title_en',
                        'price',
                        'image',
                        'provider',
                        'discounted_price',
                    )->inRandomOrder();
            }])
            ->get([
                'id',
                'name_uz',
                'name_ru',
                'name_en',
                'slug',
                'image',
                'image_mobile',
                'is_active'
            ])
            ->map(function ($category) use ($lang) {
                return [
                    'id' => $category->id,
                    'name' => [
                        'uz' => $category->name_uz,
                        'ru' => $category->name_ru,
                        'en' => $category->name_en,
                    ],
                    'slug' => $category->slug,
                    'image' => url('storage/' . $category->image),
                    'image_mobile' =>  url('storage/' . $category->image_mobile),
                    'is_active' => $category->is_active,
                    'products' => $category->bannerProducts->map(function ($product) use ($lang) {
                        return [
                            'id' => $product->product_id,
                            'title' => $product->{'title_' . $lang}, // Dynamic language field
                            'price' => round($product->price),
                            'discounted_price' => $product->discounted_price ? round($product->discounted_price) : null,
                            'discount_percent' => $product->price > 0 ? round((($product->price - $product->discounted_price) / $product->price) * 100, 2) : 0, // Discount percent calculation
                            'image' => $product->image,
                            'provider' => $product->provider,
                            'is_liked' => false,
                        ];
                    })
                ];
            });

        return response()->json(['categories' => $categories]);
    }

    /**
     * @OA\Get(
     *     path="/api/get-one-additional-category",
     *     summary="Get list of active categories with products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         description="Language code (uz, ru, en)",
     *         @OA\Schema(type="string", enum={"uz", "ru", "en"}, example="uz")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active categories with products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="categories", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="name", type="object",
     *                         @OA\Property(property="uz", type="string"),
     *                         @OA\Property(property="ru", type="string"),
     *                         @OA\Property(property="en", type="string")
     *                     ),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="image_mobile", type="string"),
     *                     @OA\Property(property="is_active", type="boolean"),
     *                     @OA\Property(property="products", type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="title", type="object",
     *                                 @OA\Property(property="uz", type="string"),
     *                                 @OA\Property(property="ru", type="string"),
     *                                 @OA\Property(property="en", type="string")
     *                             ),
     *                             @OA\Property(property="price", type="string"),
     *                             @OA\Property(property="description", type="object",
     *                                 @OA\Property(property="uz", type="string"),
     *                                 @OA\Property(property="ru", type="string"),
     *                                 @OA\Property(property="en", type="string")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getOnecategory(Request $request)
    {
        $limit = $request->query('limit', 3);
        $lang = $request->query('lang', 'uz'); // Default lang is 'uz'
        $categories = Category::where('is_active', true)->where('id', $request->category_id)
            ->with(['bannerProducts' => function ($query) use ($limit, $lang) {
                $query->limit($limit)
                    ->select(
                        'id',
                        'product_id',
                        'category_id',
                        'title_uz',
                        'title_ru',
                        'title_en',
                        'price',
                        'image',
                        'provider',
                        'discounted_price',
                    )->inRandomOrder();
            }])
            ->get([
                'id',
                'name_uz',
                'name_ru',
                'name_en',
                'slug',
                'image',
                'image_mobile',
                'is_active'
            ])
            ->map(function ($category) use ($lang) {
                return [
                    'id' => $category->id,
                    'name' => [
                        'uz' => $category->name_uz,
                        'ru' => $category->name_ru,
                        'en' => $category->name_en,
                    ],
                    'slug' => $category->slug,
                    'image' => url('storage/' . $category->image),
                    'image_mobile' =>  url('storage/' . $category->image_mobile),
                    'is_active' => $category->is_active,
                    'products' => $category->bannerProducts->map(function ($product) use ($lang) {
                        return [
                            'id' => $product->product_id,
                            'title' => $product->{'title_' . $lang}, // Dynamic language field
                            'price' => round($product->price),
                            'discounted_price' => $product->discounted_price ? round($product->discounted_price) : null,
                            'discount_percent' => $product->price > 0 ? round((($product->price - $product->discounted_price) / $product->price) * 100, 2) : 0, // Discount percent calculation
                            'image' => $product->image,
                            'provider' => $product->provider,
                            'is_liked' => false,
                        ];
                    })
                ];
            });

        return response()->json(['category' => $categories]);
    }


    /**
     * @OA\Get(
     *     path="/api/additional-categories-token",
     *     summary="Get list of active categories with products",
     *     tags={"Products"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active categories with products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="categories", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="name", type="object",
     *                         @OA\Property(property="uz", type="string"),
     *                         @OA\Property(property="ru", type="string"),
     *                         @OA\Property(property="en", type="string")
     *                     ),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="image_mobile", type="string"),
     *                     @OA\Property(property="is_active", type="boolean"),
     *                     @OA\Property(property="products", type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="title", type="object",
     *                                 @OA\Property(property="uz", type="string"),
     *                                 @OA\Property(property="ru", type="string"),
     *                                 @OA\Property(property="en", type="string")
     *                             ),
     *                             @OA\Property(property="price", type="string"),
     *                             @OA\Property(property="description", type="object",
     *                                 @OA\Property(property="uz", type="string"),
     *                                 @OA\Property(property="ru", type="string"),
     *                                 @OA\Property(property="en", type="string")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function categoriesToken(Request $request)
    {
        $limit = $request->query('limit', 3);
        $categories = Category::where('is_active', true)
            ->with(['products' => function ($query) use ($limit) {
                $query->limit($limit)
                    ->select(
                        'id',
                        'category_id',
                        'title_uz',
                        'title_ru',
                        'title_en',
                        'price',
                        'image'
                    );
            }])
            ->get([
                'id',
                'name_uz',
                'name_ru',
                'name_en',
                'slug',
                'image',
                'image_mobile',
                'is_active'
            ])
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => [
                        'uz' => $category->name_uz,
                        'ru' => $category->name_ru,
                        'en' => $category->name_en,
                    ],
                    'slug' => $category->slug,
                    'image' => url('storage/' . $category->image),
                    'image_mobile' =>  url('storage/' . $category->image_mobile),
                    'is_active' => $category->is_active,
                    'products' => $category->products->map(function ($product) {
                        $isLiked = auth()->check() && auth()->user()->likedProducts()->where('product_id', $product->id)->exists();
                        return [
                            'id' => $product->id,
                            'title' => [
                                'uz' => $product->title_uz,
                                'ru' => $product->title_ru,
                                'en' => $product->title_en,
                            ],
                            'price' => $product->price,
                            'image' => url('storage/' . $product->image),
                            'is_liked' => $isLiked,
                        ];
                    })
                ];
            });

        return response()->json(['categories' => $categories]);
    }


    /**
     * @OA\Get(
     *     path="/api/additional-products",
     *     summary="Get list of all products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=true,
     *         description="Category ID to filter products",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of products per page (limit)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=false,
     *         description="Language code (uz, ru, en)",
     *         @OA\Schema(type="string", enum={"uz", "ru", "en"}, example="uz")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="products", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="object",
     *                         @OA\Property(property="uz", type="string"),
     *                         @OA\Property(property="ru", type="string"),
     *                         @OA\Property(property="en", type="string")
     *                     ),
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="price", type="string"),
     *                     @OA\Property(property="rating", type="number", format="float")
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="next_page_url", type="string"),
     *                 @OA\Property(property="prev_page_url", type="string"),
     *                 @OA\Property(property="page_count", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function additionalProducts(Request $request)
    {
        $category = Category::where('is_active', true)->where('id', $request->category_id)->first();
        $lang = $request->query('lang', 'uz'); // Default language is 'uz'

        $limit = $request->query('limit', 10); // Default limit is 10
        $products = BannerProduct::select(
            'id',
            'product_id',
            'category_id',
            'title_uz',
            'title_ru',
            'title_en',
            'price',
            'image',
            'provider',
            'discounted_price',
        )->inRandomOrder()->where('category_id', $category->id)->paginate($limit);

        $mappedProducts = $products->getCollection()->map(function ($product) use ($lang) {
            return [
                'id' => $product->product_id,
                'title' => $product->{'title_' . $lang}, // Dynamic language field
                'price' => round($product->price),
                'discounted_price' => $product->discounted_price ? round($product->discounted_price) : null,
                'discount_percent' => $product->price > 0 ? round((($product->price - $product->discounted_price) / $product->price) * 100, 2) : 0,
                'image' => $product->image,
                'provider' => $product->provider,
                'is_liked' => false,
            ];
        });

        return response()->json([
            'products' => $mappedProducts,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }



    /**
     * @OA\Get(
     *     path="/api/additional-products-token",
     *     summary="Get list of all products",
     *     security={{ "bearerAuth":{} }},
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="object",
     *                     @OA\Property(property="uz", type="string"),
     *                     @OA\Property(property="ru", type="string"),
     *                     @OA\Property(property="en", type="string")
     *                 ),
     *                 @OA\Property(property="image", type="string"),
     *                 @OA\Property(property="price", type="string"),
     *                 @OA\Property(property="rating", type="number", format="float")
     *             )
     *         )
     *     )
     * )
     */
    public function additionalProductsToken()
    {
        $products = Product::select(
            'id',
            'title_uz',
            'title_ru',
            'title_en',
            'image',
            'price'
        )
            ->where('is_active', true)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => [
                        'uz' => $product->title_uz,
                        'ru' => $product->title_ru,
                        'en' => $product->title_en,
                    ],
                    'image' => url('storage/' . $product->image),
                    'price' => $product->price
                ];
            });

        return response()->json($products);
    }
}
