<?php

namespace App\Http\Controllers\Api\Products;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductDetailResource;
use OpenApi\Annotations as OA;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\Category;
use App\Traits\ApiResponseHelpers;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    use ApiResponseHelpers;

    private $apiKey;

    public function __construct()
    {
        $this->apiKey = "8e6d9b54-88a7-4dcc-9f2b-add4d68dbbb8";
    }

    /**
     * @OA\Get(
     *     path="/api/categories-from-taobao",
     *     summary="Get categories from taobao",
     *     tags={"Products"},
     *     @OA\Response(response="200", description="Categories from taobao"),
     * )
     */
    public function categoriesFromTaoBao()
    {
        $categories = Category::all();

        return $this->respondWithSuccess([
            'categories' => CategoryResource::collection($categories)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/sub-categories-from-taobao",
     *     summary="Get subcategories from taobao",
     *     tags={"Products"},
     *      @OA\Parameter(
     *         name="categoryId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the category"
     *     ),
     *     @OA\Response(response="200", description="SubCategories from taobao"),
     * )
     */
    public function subCategoriesFromTaoBao(Request $request)
    {
        $categoryId = $request->categoryId;

        $url = "http://otapi.net/service-json/GetCategorySubcategoryInfoList?instanceKey={$this->apiKey}&language=uz&signature=221bb03db72a7ec4680e84de68f0ec37f158fcac66693ee9cc7083e14adac06c&timestamp=20240526084418&parentCategoryId=$categoryId";

        $response = Http::get($url);
        $categories = $response->json();

        return $this->respondWithSuccess([
            'sub_categories' => SubCategoryResource::collection($categories['CategoryInfoList']['Content'])
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products-from-taobao",
     *     summary="Get products from taobao",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="subCategoryId",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the subcategory"
     *     ),
     *     @OA\Parameter(
     *         name="productsCount",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="The count of products"
     *     ),
     *     @OA\Response(response="200", description="Products from taobao"),
     * )
     */
    public function productsFromTaoBao(Request $request)
    {
        $subCategoryId = $request->subCategoryId;
        $productsCount = $request->productsCount ? $request->productsCount : 20;
        $url = "http://otapi.net/service-json/BatchSearchItemsFrame?instanceKey={$this->apiKey}&language=uz&signature=ede6e796a3f325d3d0a7743c7c4e7d93648c74d7c41744f1671714e327b5d52b&timestamp=20240526085107&sessionId=&xmlParameters=%3CSearchItemsParameters%3E%3CCategoryId%3E$subCategoryId%3C%2FCategoryId%3E%3C%2FSearchItemsParameters%3E&framePosition=0&frameSize=$productsCount&blockList=";

        $response = Http::get($url);
        $products = $response->json();

        return $this->respondWithSuccess([
            'products' => ProductResource::collection($products['Result']['Items']['Items']['Content'])
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
     *     @OA\Response(response="200", description="Product detail from taobao"),
     * )
     */
    public function productDetailFromTaoBao(Request $request)
    {
        $productId = $request->productId;
        $url = "http://otapi.net/service-json/BatchGetItemFullInfo?instanceKey={$this->apiKey}&language=uz&signature=a67f4af4c43ea96f880740611d680127cbdf84ddbdaab674d8c3f265df1353e0&timestamp=20240526085023&sessionId=&itemParameters=&itemId=$productId&blockList=OriginalDescription";

        $response = Http::get($url);
        $productDetail = $response->json();

        return $this->respondWithSuccess([
            'detail' => new ProductDetailResource($productDetail['Result']['Item'])
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
     *     @OA\Response(response="200", description="Products from taobao"),
     * )
     */
    public function searchProductsFromTaobao(Request $request)
    {
        $searchQuery = $request->searchQuery;
        $productsCount = $request->productsCount ? $request->productsCount : 20;

        $url = "http://otapi.net/service-json/BatchSearchItemsFrame?instanceKey={$this->apiKey}&language=uz&signature=f577361a7599024bbbf86517fc0f5ed3b9938c76c60fdb75a3fb66e350b702cd&timestamp=20240606182926&sessionId=&xmlParameters=%3CSearchItemsParameters%3E%3CItemTitle%3E$searchQuery%3C%2FItemTitle%3E%3C%2FSearchItemsParameters%3E&framePosition=0&frameSize=$productsCount&blockList=";

        $response = Http::get($url);
        $products = $response->json();

        return $this->respondWithSuccess([
            'products' => ProductResource::collection($products['Result']['Items']['Items']['Content'])
        ]);
    }
}
