<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Basket;
use App\Models\BasketItemColor;
use App\Models\BasketItemSize;
use App\Models\CardNumber;
use App\Models\Comment;
use App\Models\Exchanges;
use App\Models\Income;
use App\Models\Order;
use App\Models\OrderCheck;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class OrderController extends Controller
{
    protected $productController;

    public function __construct(ProductController $productController)
    {
        $this->productController = $productController;
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Order"},
     *     summary="Create a new order",
     *     description="Create a new order with items",
     *     operationId="createOrder",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Order details",
     *         @OA\JsonContent(
     *             required={"items", "userData", "location", "total_price"},
     *             @OA\Property(property="userData", type="object", 
     *                 @OA\Property(property="name", type="string", example="John"),
     *                 @OA\Property(property="phone", type="string", example="+380671234567")
     *             ),
     *             @OA\Property(property="location", type="object",
     *                 @OA\Property(property="addressText", type="string", example="Uzbekistan"),
     *                 @OA\Property(property="url", type="string", example="https://google.com"),
     *             ),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="string", example="1"),
     *                 @OA\Property(property="title", type="string", example="title"),
     *                 @OA\Property(property="image_url", type="string", example="url"),
     *                 @OA\Property(property="quantity", type="integer", example="2"),
     *                 @OA\Property(property="color", type="string", example="black"),
     *                 @OA\Property(property="size", type="string", example="xl"),
     *                 @OA\Property(property="size_id", type="number", example="4"),
     *                 @OA\Property(property="price", type="number", example="4.99")
     *             )),
     *             @OA\Property(property="total_price", type="number", example="50.99")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="order_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid data")
     *         )
     *     )
     * )
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'userData' => 'required|array',
            'location' => 'required|array',
            'userData.name' => 'required|string',
            'userData.phone' => 'required|string',
            'location.addressText' => 'required|string',
            'location.url' => 'nullable|string',
            'items.*.product_id' => 'required|string',
            'items.*.title' => 'required|string',
            'items.*.image_url' => 'required|url',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.color' => 'required|string',
            'items.*.size' => 'required|string',
            'items.*.size_id' => 'required|integer',
            'items.*.price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid_data', 'errors' => $validator->errors(), 'error' => true], 400);
        }

        $user = auth()->user();

        // Update user location fields if they are empty
        // $user->region = $user->region ?: $request->location['region'] ?? null;
        // $user->district = $user->district ?: $request->location['district'] ?? null;
        // $user->street = $user->street ?: $request->location['street'] ?? null;
        // $user->house = $user->house ?: $request->location['house'] ?? null;
        // $user->post_index = $user->post_index ?: $request->location['post_index'] ?? null;
        // $user->location = $user->location ?: $request->location['url'] ?? null;
        // $user->save();

        $order = DB::transaction(function () use ($request, $user) {
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $request->total_price,
                'user_name' => $request->userData['name'],
                'user_phone' => $request->userData['phone'],
                'location_url' => $request->location['url'],
                'location_name' => $request->location['addressText'],
                'status' => '2',
                'substatus' => '1',
                'status_color' => '#05ff00',
                'is_checking' => false
            ]);

            $client = new Client();

            foreach ($request->items as $item) {
                // Fetch product details (adjust this part according to your logic)
                $productDetailResponse = $this->productController->productDetailFromTaoBao(new Request(['productId' => $item['product_id'], 'lang' => 'ru']));
                $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];

                // Download image and save to storage
                $url = $item['image_url'];

                // Generate a random filename
                $randomName = Str::random(40) . '.' . pathinfo($url, PATHINFO_EXTENSION);
                $imagePath = 'order_images/' . $randomName;

                // Using Guzzle to download the image
                $response = $client->get($url);
                Storage::disk('public')->put($imagePath, $response->getBody());

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'title' => $productDetail['title'],
                    'image_url' => $imagePath,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'color' => $item['color'],
                    'size' => $item['size'],
                    'product_url' => 'ok.com',
                ]);

                // Remove basket items associated with the size_id and color_id
                $basketItemSize = BasketItemSize::where('id', $item['size_id'])->first();
                if ($basketItemSize) {
                    $basketId = $basketItemSize->basket_id;
                    $basketItemSize->delete();

                    // Remove associated BasketItemColor
                    $basketItemColor = BasketItemColor::where('id', $basketItemSize['color_id'])->first();
                    if ($basketItemColor) {
                        $basketItemColor->delete();
                    }

                    // Check if the basket has no more sizes or colors, then delete the basket
                    if (
                        !BasketItemSize::where('basket_id', $basketId)->exists() &&
                        !BasketItemColor::where('id', $basketItemSize['color_id'])->exists()
                    ) {
                        Basket::where('id', $basketId)->delete();
                    }
                }
            }

            // Check if the order requires checking
            if (OrderCheck::where('order_id', $order->id)->exists()) {
                $order->is_checking = true;
                $order->save();
            }

            return $order;
        });

        return response()->json(['message' => 'order_created_successfully', 'order_id' => $order->id], 201);
    }


    /**
     * @OA\Get(
     *     path="/api/get-order",
     *     tags={"Order"},
     *     summary="Get order by order_id",
     *     description="Get order by order_id",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID of the order"
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Get order",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Get order"),
     *             @OA\Property(property="order_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid data")
     *         )
     *     )
     * )
     */

    public function getOrder(Request $request)
    {
        $order = Order::where('id', $request->order_id)->where('user_id', auth()->user()->id)->first();
        $cardNumber = CardNumber::orderBy('money', 'desc')->where('type', 'order')->where('is_active', 1)->first();
        $kgPrice = Exchanges::where('currency', 'kg')->first();

        return response()->json(['message' => 'successfull', 'order' => ['id' => $order->id, 'total_price' => $order->total_price, 'kg_price' => $kgPrice->amount, 'card_number' => $cardNumber->card_number ?? 9860176612846848], 'detail' => new OrderResource($order)], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/get-cargo",
     *     summary="Get total cargo weight",
     *     description="Get cargo",
     *     tags={"Order"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID of the order"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function getCargo(Request $request)
    {
        $user = $request->user();
        $order = Order::where('user_id', $user->id)->where('id', $request->order_id)->first();
        $cardNumber = CardNumber::orderBy('money', 'desc')->where('type', 'cargo')->where('is_active', 1)->first();
        $kgPrice = Exchanges::where('currency', 'kg')->first();

        return response()->json(['message' => 'successfull', 'order' => ['id' => $order->id, 'total_price' => $order->kg * $kgPrice->amount, 'kg' => $order->kg ?? 0, 'kg_price' => $kgPrice->amount ?? 0, 'card_number' => $cardNumber->card_number ?? 0]], 201);
    }


    /**
     * @OA\Post(
     *     path="/api/add-check",
     *     tags={"Order"},
     *     summary="Create a new order check",
     *     description="Create a new order check with order_id and image",
     *     operationId="createOrderCheck",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Order check details",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"order_id", "image", "type"},
     *                 @OA\Property(property="order_id", type="integer", example="1"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="type", type="string", example="order"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order check created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order check created successfully"),
     *             @OA\Property(property="order_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid data")
     *         )
     *     )
     * )
     */

    public function addCheck(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'image' => 'required|image',
            'type' => 'required|in:order,cargo',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Transaction block
        $result = DB::transaction(function () use ($request, $order) {
            // Check for OrderCheck limit
            if (OrderCheck::where('order_id', $request->order_id)->count() > 4) {
                throw new \Exception('Order checks limit reached');
            }

            // Create income record
            Income::create([
                'user_id' => auth()->user()->id,
                'order_id' => $request->order_id,
                'amount' => $order->total_price,
                'type' => $request->type,
            ]);

            // Find the appropriate card number
            $cardNumber = CardNumber::orderBy('money', 'desc')
                ->where('type', $request->type)
                ->where('is_active', 1)
                ->first();

            if (!$cardNumber) {
                throw new \Exception('No active card number found');
            }

            // Create order check
            $orderCheck = OrderCheck::create([
                'order_id' => $request->order_id,
                'image' => $request->image->store('order_checks', 'public'), // Store the image and save the path
                'card_number_id' => $cardNumber->id,
            ]);

            // Update order status
            $order->update(['status' => "6", 'substatus' => null]);
            $order->save();

            return $orderCheck;
        });

        if ($result instanceof \Exception) {
            return response()->json(['message' => $result->getMessage()], 400);
        }

        return response()->json(['message' => 'Order check created successfully', 'order' => $result], 201);
    }




    /**
     * @OA\Post(
     *     path="/api/user-basket",
     *     tags={"Order"},
     *     summary="Create Baskets",
     *     description="Create multiple Baskets with items",
     *     operationId="createBaskets",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="List of Baskets details",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 required={"product_id", "sizes"},
     *                 @OA\Property(property="product_id", type="string", example="1"),
     *                 @OA\Property(property="sizes", type="array", @OA\Items(
     *                     @OA\Property(property="value", type="string", example="xl"),
     *                     @OA\Property(property="quantity", type="integer", example="2"),
     *                     @OA\Property(property="color_id", type="string", example="2"),
     *                 )),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Baskets created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Products added to baskets"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid data"),
     *         ),
     *     ),
     * )
     */
    public function userBasket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.product_id' => 'required|string',
            '*.sizes' => 'required|array',
            '*.sizes.*.value' => 'required|string',
            '*.sizes.*.quantity' => 'required|integer|min:1',
            '*.sizes.*.color_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $baskets = [];

        foreach ($request->all() as $item) {
            $basket = Basket::create([
                'user_id' => auth()->user()->id,
                'product_id' => $item['product_id'],
            ]);

            foreach ($item['sizes'] as $size) {
                BasketItemSize::create([
                    'basket_id' => $basket->id,
                    'name' => $size['value'],
                    'quantity' => $size['quantity'],
                    'color_id' => $size['color_id'],
                ]);
            }

            $baskets[] = $basket;
        }

        return response()->json(['message' => 'Products added to baskets', 'baskets' => $baskets], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/get-orders",
     *     tags={"Order"},
     *     summary="Get orders for authenticated user",
     *     description="Retrieve orders created by the authenticated user",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         required=false,
     *         description="Filter orders by status",
     *         @OA\Schema(
     *             type="string",
     *             enum={"all", "active", "in_verification"}
     *         )
     *     ),
     *  @OA\Parameter(
     *              name="lang",
     *              in="query",
     *              required=false,
     *              @OA\Schema(
     *                  type="string",
     *                     enum={"uz", "ru", "en"}
     *              ),
     *              description="Language code for the product details. Available options: uz, ru, en"
     *          ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="orders",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="items", type="array", @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="price", type="number"),
     *                         @OA\Property(property="image_url", type="string"),
     *                     ))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *         ),
     *     )
     * )
     */
    public function getOrders(Request $request)
    {
        $user = $request->user();
        $items = [];

        $filter = $request->query('filter', 'all');

        $verificationStatuses = OrderStatus::where('type', 'in_verification')->pluck('id');
        $activeStatuses = OrderStatus::where('type', 'active')->pluck('id');

        $ordersQuery = Order::where('user_id', $user->id);

        if ($filter === 'in_verification') {
            $ordersQuery->whereIn('status', $verificationStatuses);
        } elseif ($filter === 'active') {
            $ordersQuery->whereIn('status', $activeStatuses);
        }

        $orders = $ordersQuery->with(['getStatus', 'getSubStatus'])->get();
        return response()->json(['orders' => OrderResource::collection($orders)], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/get-order-items",
     *     tags={"Order"},
     *     summary="Get order items for authenticated user",
     *     description="Retrieve order items created by the authenticated user",
     *     security={{ "bearerAuth":{} }},
     *  @OA\Parameter(
     *              name="lang",
     *              in="query",
     *              required=false,
     *              @OA\Schema(
     *                  type="string",
     *                     enum={"uz", "ru", "en"}
     *              ),
     *              description="Language code for the product details. Available options: uz, ru, en"
     *          ),
     *  @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID of the order"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *         ),
     *     )
     * )
     */
    public function getOrderItems(Request $request)
    {
        $user = $request->user();

        // Fetch orders for the user
        $orders = Order::where('user_id', $user->id)->get();
        $items = [];

        // Iterate over each order
        foreach ($orders as $order) {
            // Iterate over each item in the order
            foreach ($order->items as $item) {
                // Fetch product details
                $productDetailResponse = app(ProductController::class)->productDetailFromTaoBao(new Request([
                    'productId' => $item['product_id'],
                    'lang' => $request->lang
                ]));
                $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];

                // Add enriched item to the items array
                $items[] = [
                    'id' => $item['id'],
                    'product_id' => $productDetail['id'],
                    'order_id' => $order->id,
                    'title' => $productDetail['title'],
                    'price' => $productDetail['price'],
                    'image_url' => $productDetail['image'],
                ];
            }
        }

        // Return the enriched items
        return response()->json(['items' => $items], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/get-user-basket",
     *     tags={"Order"},
     *     summary="Get basket",
     *     security={{ "bearerAuth":{} }},
     *  @OA\Parameter(
     *              name="lang",
     *              in="query",
     *              required=false,
     *              @OA\Schema(
     *                  type="string",
     *                     enum={"uz", "ru", "en"}
     *              ),
     *              description="Language code for the product details. Available options: uz, ru, en"
     *          ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *         ),
     *     ),
     * )
     */
    public function getUserBasket(Request $request)
    {
        $user = $request->user();
        $baskets = Basket::where('user_id', $user->id)->with('sizes')->get();
        $result = [];

        foreach ($baskets as $basket) {
            // Simulating product details fetch
            $productDetailResponse = $this->productController->productDetailFromTaoBao(new Request(['productId' => $basket['product_id'], 'lang' => $request->lang]));
            // return $productDetailResponse;
            $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];
            // return $productDetail;

            $productColors = collect($productDetail['colors']);
            $productSizes = collect($productDetail['sizes']);

            $groupedSizes = $basket->sizes->groupBy('color_id');

            foreach ($groupedSizes as $color_id => $sizes) {
                // return $color_id;
                $colorDetail = $productColors->firstWhere('id', $color_id);
                if ($colorDetail) {
                    foreach ($sizes as $size) {
                        $sizeDetail = $productSizes->firstWhere('id', $size->size_id);
                        $result[] = [
                            'id' => $basket['id'],
                            'product_id' => "{$productDetail['id']}" ?? null,
                            'title' => $productDetail['title'] ?? null,
                            'size' => $size->name ?? null,
                            'size_id' => $size->id ?? null,
                            'color' => $colorDetail['value'] ?? null,
                            'quantity' => $size->quantity ?? 1,
                            'image_url' => $colorDetail['image_url'] ?? null,
                            'price' => $productDetail['price'] ?? null,
                            'product_url' => $productDetail['product_url'] ?? null,
                        ];
                    }
                }
            }
        }

        return response()->json($result);
    }

    /**
     * @OA\Get(
     *     path="/api/user-products",
     *     summary="Get products based on comment status",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         required=true,
     *         description="Filter for commented or non_commented products",
     *         @OA\Schema(
     *             type="string",
     *             enum={"commented", "non_commented"}
     *         )
     *     ),
     *     @OA\Response(response="200", description="List of products based on filter"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="400", description="Invalid filter value")
     * )
     */
    public function getUserProducts(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $filter = $request->query('filter');

        if (!in_array($filter, ['commented', 'non_commented'])) {
            return response()->json(['message' => 'Invalid filter value'], 400);
        }

        if ($filter === 'commented') {
            $orders = Order::where('user_id', $user->id)
                ->whereHas('comments', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['comments' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])
                ->get();

            $result = $orders->map(function ($order) {
                $comment = $order->comments->first();
                return [
                    'id' => $order->id,
                    'image_url' => url('storage/' . $order->items->first()->image_url),
                    'name' => $order->items->first()->title,
                    'date' => $comment->created_at->format('Y-m-d'),
                    'stars' => $comment->rating,
                    'comment' => $comment->name,
                    'photos' => array_map(function ($image) {
                        return url('storage/' . $image);
                    }, $comment->images ?? []),
                ];
            });

            return response()->json(['data' => $result]);
        } else {
            $orders = Order::where('user_id', $user->id)
                ->whereDoesntHave('comments', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            $result = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'image_url' => url('storage/' . $order->items->first()->image_url),
                    'name' => $order->items->first()->title,
                    'date' => $order->created_at->format('Y-m-d'),
                ];
            });

            return response()->json(['data' => $result]);
        }
    }







    /**
     * @OA\Put(
     *     path="/api/add",
     *     tags={"Order"},
     *     summary="Update size quantity in the user's basket",
     *     description="Update the quantity of a specific size in the user's basket",
     *     operationId="updateBasketItemSizeQuantity",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Basket item size details",
     *         @OA\JsonContent(
     *             required={"basket_id", "size_id", "quantity"},
     *             @OA\Property(property="basket_id", type="integer", example="1"),
     *             @OA\Property(property="size_id", type="integer", example="4"),
     *             @OA\Property(property="quantity", type="integer", example="2"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Size quantity updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Size quantity updated successfully"),
     *             @OA\Property(property="basket_item_size", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid data"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Basket item size not found"),
     *         ),
     *     ),
     * )
     */
    public function updateBasketItemSizeQuantity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'basket_id' => 'required|integer|exists:baskets,id',
            'size_id' => 'required|integer|exists:basket_item_sizes,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $basketItemSize = BasketItemSize::where('basket_id', $request->basket_id)
            ->where('id', $request->size_id)
            ->first();
        // return $basketItemSize;

        if (!$basketItemSize) {
            return response()->json(['message' => 'Basket item size not found'], 404);
        }

        $basketItemSize->quantity = $request->quantity;
        $basketItemSize->save();

        return response()->json(['message' => 'Size quantity updated successfully', 'basket_item_size' => $basketItemSize], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/remove",
     *     tags={"Order"},
     *     summary="Remove size and color from the user's basket",
     *     description="Remove a specific size and associated color by size_id and color_id in the user's basket",
     *     operationId="removeBasketItem",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Basket item details",
     *         @OA\JsonContent(
     *             required={"basket_id", "size_id"},
     *             @OA\Property(property="basket_id", type="integer", example="1"),
     *             @OA\Property(property="size_id", type="integer", example="4"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Basket item removed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Basket item removed successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid data"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Basket item size not found"),
     *         ),
     *     ),
     * )
     */
    public function removeBasketItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'basket_id' => 'required|integer',
            'size_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        // Find the basket item size by basket_id and size_id
        $basketItemSize = BasketItemSize::where('basket_id', $request->basket_id)
            ->where('id', $request->size_id)
            ->first();

        if (!$basketItemSize) {
            return response()->json(['message' => 'Basket item size not found'], 404);
        }

        // Remove the size and associated colors from the basket
        $basketItemSize->delete();

        return response()->json($this->getUserBasket($request), 200);
    }
}
