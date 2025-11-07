/**
* Handles the API call to retrieve product details from OneBound API for a given product ID.
*
* @param Request $request
* @return \Illuminate\Http\JsonResponse
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