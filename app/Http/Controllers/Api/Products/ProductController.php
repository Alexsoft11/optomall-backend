<?php

namespace App\Http\Controllers\Api\Products;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductDetailTokenResource;
use OpenApi\Annotations as OA;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductTokenResource;
use App\Http\Resources\ScoreResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\Category;
use App\Models\LikedProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TaobaoCategory;
use App\Traits\ApiResponseHelpers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponseHelpers;

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
     *             @OA\Items(type="string")
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
        $validator = Validator::make(['product_ids' => $request->all()], [
            'product_ids' => 'required|array',
            'product_ids.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors(), 'error' => true], 400);
        }

        $userId = auth()->user()->id;
        $productIds = $request->all();
        $messages = [];

        foreach ($productIds as $productId) {
            // return $productId;
            $likedProduct = LikedProduct::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($likedProduct) {
                $likedProduct->delete();
                $messages[] = "Product ID {$productId} successfully removed";
            } else {
                $productDetailResponse = $this->productDetailFromTaoBao(new Request(['productId' => $productId, 'lang' => 'uz']));
                $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];
                // return $productDetail;
                LikedProduct::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'title_uz' => $productDetail['title'],
                    'title_en' => $productDetail['title'],
                    'title_ru' => $productDetail['title'],
                    'image' => $productDetail['image'],
                    'price' => $productDetail['price'],
                ]);
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
            $productDetailResponse = $this->productDetailFromTaoBao(new Request(['productId' => $product->product_id, 'lang' => $lang]));
            $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];

            return [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'title' => $productDetail['title'],
                'price' => $productDetail['price'],
                'image' => $productDetail['image'],
                'is_liked' => true,
            ];
        });

        return response()->json($likedProductsArray, 200);
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
        $lang = $request->query('lang', 'en');
        $categories = TaobaoCategory::where('is_active', true)->get();

        $categoriesMap = $categories->map(function ($category) use ($lang) {
            $category->name = $category->{"name_$lang"};
            $category->subcategories = $category->subcategories->map(function ($subcategory) use ($lang) {
                $subcategory->name = $subcategory->{"subcategory_name_$lang"};
                $subcategory->items = $subcategory->items->map(function ($item) use ($lang) {
                    $item->name = $item->{"subcategory_$lang"};
                    return $item;
                });
                return $subcategory;
            });
            return $category;
        });

        return $this->respondWithSuccess([
            'categories' => CategoryResource::collection($categoriesMap)
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
        $lang = $request->lang;
        $page = $request->page ? $request->page : 1;
        $productsCount = $request->productsCount ? $request->productsCount : 20;
        $url = "https://api.kongcrdv.com/taobao/api_call.php?api_name=item_search&lang=$lang&q=$subCategory&page=$page&page_size=$productsCount&key=kingdev5809@gmail.com";

        $response = Http::get($url);
        $products = $response->json();

        return $this->respondWithSuccess([
            'page' => $page, 'page_size' => $productsCount, 'products' => ProductResource::collection($products['items']['item'])
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
        $lang = $request->lang;
        $page = $request->page ? $request->page : 1;
        $productsCount = $request->productsCount ? $request->productsCount : 20;
        $url = "https://api.kongcrdv.com/taobao/api_call.php?api_name=item_search&lang=$lang&q=$subCategory&page=$page&page_size=$productsCount&key=kingdev5809@gmail.com";

        $response = Http::get($url);
        $products = $response->json();

        return $this->respondWithSuccess([
            'page' => $page, 'page_size' => $productsCount, 'products' => ProductTokenResource::collection($products['items']['item'])
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
        $lang = $request->lang;
        $provider = $request->provider ?? 'taobao';
        $url = "https://api.kongcrdv.com/$provider/api_call.php?api_name=item_get&lang=$lang&num_iid=$productId&key=kingdev5809@gmail.com";

        $response = Http::get($url);
        $productDetail = $response->json();

        // Check if product detail contains item
        if (!isset($productDetail['item'])) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $imageSearchProvider = $provider;
        $imageSearchUrl = "https://kongcrdv.com/$imageSearchProvider/api/export_api.php?action=item_search_img&key=kingdev5809@gmail.com&imgid={$productDetail['item']['pic_url']}&lang=$lang";
        $rand = Http::get($imageSearchUrl);
        $randomProducts = $rand->json();

        // Check if randomProducts contains data.items.item
        if (!isset($randomProducts['data']['items']['item']) || empty($randomProducts['data']['items']['item'])) {
            $imageSearchProvider = $provider === 'taobao' ? '1688' : 'taobao';
            $imageSearchUrl = "https://kongcrdv.com/$imageSearchProvider/api/export_api.php?action=item_search_img&key=kingdev5809@gmail.com&imgid={$productDetail['item']['pic_url']}&lang=$lang";
            $rand = Http::get($imageSearchUrl);
            $randomProducts = $rand->json();
        }

        $recommendedProducts = collect($randomProducts['data']['items']['item'])->take(5);

        return $this->respondWithSuccess([
            'detail' => new ProductDetailResource($productDetail['item']),
            'score' => [
                'rating' => $productDetail['item']['seller_info']['item_score'] ?? 4.6,
                'sold' => $productDetail['item']['sales']
            ],
            'recommended' => ProductResource::collection($recommendedProducts),
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
        $lang = $request->lang;
        $provider = $request->provider ?? 'taobao';
        $url = "https://api.kongcrdv.com/$provider/api_call.php?api_name=item_get&lang=$lang&num_iid=$productId&key=kingdev5809@gmail.com";

        $response = Http::get($url);
        $productDetail = $response->json();

        // Check if product detail contains item
        if (!isset($productDetail['item'])) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $imageSearchProvider = $provider;
        $imageSearchUrl = "https://kongcrdv.com/$imageSearchProvider/api/export_api.php?action=item_search_img&key=kingdev5809@gmail.com&imgid={$productDetail['item']['pic_url']}&lang=$lang";
        $rand = Http::get($imageSearchUrl);
        $randomProducts = $rand->json();

        // Check if randomProducts contains data.items.item
        if (!isset($randomProducts['data']['items']['item']) || empty($randomProducts['data']['items']['item'])) {
            $imageSearchProvider = $provider === 'taobao' ? '1688' : 'taobao';
            $imageSearchUrl = "https://kongcrdv.com/$imageSearchProvider/api/export_api.php?action=item_search_img&key=kingdev5809@gmail.com&imgid={$productDetail['item']['pic_url']}&lang=$lang";
            $rand = Http::get($imageSearchUrl);
            $randomProducts = $rand->json();
        }

        $recommendedProducts = collect($randomProducts['data']['items']['item'])->take(5);

        return $this->respondWithSuccess([
            'detail' => new ProductDetailTokenResource($productDetail['item']),
            'score' => [
                'rating' => $productDetail['item']['seller_info']['item_score'] ?? 4.6,
                'sold' => $productDetail['item']['sales']
            ],
            'recommended' => ProductTokenResource::collection($recommendedProducts),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search-products-from-taobao",
     *     summary="Search products by title.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="searchQuery",
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
    public function searchProductsFromTaobao(Request $request)
    {
        $searchQuery = $request->searchQuery;
        $startPrice = $request->startPrice;
        $endPrice = $request->endPrice;
        $page = $request->page ? $request->page : 1;
        $productsCount = $request->productsCount ? $request->productsCount : 20;
        $provider = $request->provider ? $request->provider : 'taobao';
        $lang = $request->lang;

        $url = "https://api.kongcrdv.com/$provider/api_call.php?api_name=item_search&lang=$lang&key=kingdev5809@gmail.com&q=$searchQuery&start_price=$startPrice&end_price=$endPrice&page=$page&page_size=$productsCount";

        $response = Http::get($url);
        $products = $response->json();
        // return $products;

        return $this->respondWithSuccess([
            'products' => ProductResource::collection($products['items']['item'])
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search-products-from-taobao-token",
     *     summary="Search products by title.",
     *     tags={"Products"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="searchQuery",
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
        $searchQuery = $request->searchQuery;
        $startPrice = $request->startPrice;
        $endPrice = $request->endPrice;
        $page = $request->page ? $request->page : 1;
        $productsCount = $request->productsCount ? $request->productsCount : 20;
        $provider = $request->provider ? $request->provider : 'taobao';
        $lang = $request->lang;

        $url = "https://api.kongcrdv.com/$provider/api_call.php?api_name=item_search&lang=$lang&key=kingdev5809@gmail.com&q=$searchQuery&start_price=$startPrice&end_price=$endPrice&page=$page&page_size=$productsCount";

        $response = Http::get($url);
        $products = $response->json();
        // return $products;

        return $this->respondWithSuccess([
            'products' => ProductTokenResource::collection($products['items']['item'])
        ]);
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
     *             type="string",
     *             enum={"热销", "推荐"}
     *         )
     *     ),
     *    @OA\Parameter(
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
     *     tags={"Products"},
     *     @OA\Response(response="200", description="Products from taobao"),
     * )
     */

    public function products(Request $request)
    {
        // Retrieve the query parameters
        $query = $request->input('query');
        $lang = $request->input('lang');
        $productsCount = $request->input('productsCount');
        $page = $request->input('page');

        $url = "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=$query&lang=$lang";

        $response = Http::get($url);
        $products = $response->json();

        return $this->respondWithSuccess([
            'products' => ProductResource::collection($products['items']['item']),
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
        $productsCount = $request->input('productsCount');
        $page = $request->input('page');

        $url = "https://kongcrdv.com/taobao/api/export_api.php?action=item_search&key=kingdev5809@gmail.com&page=$page&page_size=$productsCount&q=$query&lang=$lang";

        $response = Http::get($url);
        $products = $response->json();

        return $this->respondWithSuccess([
            'products' => ProductResource::collection($products['items']['item']),
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
                        return [
                            'id' => $product->id,
                            'title' => [
                                'uz' => $product->title_uz,
                                'ru' => $product->title_ru,
                                'en' => $product->title_en,
                            ],
                            'price' => $product->price,
                            'image' => url('storage/' . $product->image),
                            'is_liked' => false,
                        ];
                    })
                ];
            });

        return response()->json(['categories' => $categories]);
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
    public function additionalProducts()
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
