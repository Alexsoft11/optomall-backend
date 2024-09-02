<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\BasketResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\CardNumber;
use App\Models\Comment;
use App\Models\Exchanges;
use App\Models\Income;
use App\Models\Order;
use App\Models\OrderCheck;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Properties;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCreated;
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
     *             required={"items", "total_price"},
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 required={"product_id", "title", "price", "quantity", "image_url", "seller_id", "provider", "sku_id", "properties", "quantityRanges"},
     *                 @OA\Property(property="product_id", type="string", example="1"),
     *                 @OA\Property(property="title", type="string", example="title"),
     *                 @OA\Property(property="image_url", type="string", example="url"),
     *                 @OA\Property(property="quantity", type="integer", example="2"),
     *                 @OA\Property(property="price", type="number", example="4.99"),
     *                 @OA\Property(property="quantityRanges", type="array", @OA\Items(
     *                     @OA\Property(property="minQuantity", type="integer", example="2"),
     *                     @OA\Property(property="price", type="number", format="float", example="411930")
     *                 )),
     *                 @OA\Property(property="seller_id", type="string", example="2"),
     *                 @OA\Property(property="provider", type="string", example="taobao"),
     *                 @OA\Property(property="sku_id", type="string", example="SKU1234567890"),
     *                 @OA\Property(property="product_url", type="string", example="url"),
     *                 @OA\Property(property="properties", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="name", type="string", example="name"),
     *                     @OA\Property(property="value", type="string", example="value")
     *                 )),
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
     *             @OA\Property(property="order_group", type="integer", example="1")
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
            'items.*.product_id' => 'required|string',
            'items.*.title' => 'required|string',
            'items.*.image_url' => 'required|url',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantityRanges' => 'nullable|array',
            'items.*.quantityRanges.*.minQuantity' => 'nullable|integer|min:1',
            'items.*.quantityRanges.*.price' => 'nullable|numeric',
            'items.*.seller_id' => 'nullable|string',
            'items.*.provider' => 'nullable|string',
            'items.*.sku_id' => 'required|string',
            'items.*.product_url' => 'nullable|string',
            'items.*.properties' => 'required|array',
            'items.*.properties.*.id' => 'required|string',
            'items.*.properties.*.name' => 'required|string',
            'items.*.properties.*.value' => 'required|string',
            'total_price' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid_data', 'errors' => $validator->errors(), 'error' => true], 400);
        }

        $items = collect($request->items);
        $groupedItems = $items->groupBy('seller_id');

        $orders = [];
        $orderGroup = null;

        DB::transaction(function () use ($groupedItems, $user, $request, &$orders, &$orderGroup) {
            $defaultStatus = OrderStatus::where('for_first', true)->first();
            $statusId = $defaultStatus ? $defaultStatus->id : '3';
            $orderGroup = OrderGroup::create([
                'status' => $statusId,
                'status_color' => '#05ff00',
                'total_price' => $request->total_price,
                'user_id' => $user->id,
                'is_paid' => false,
            ]);

            $client = new Client();

            foreach ($groupedItems as $sellerId => $items) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'total_price' => $items->reduce(function ($carry, $item) {
                        return $carry + ($item['price'] * $item['quantity']);
                    }, 0),
                    'seller_id' => $sellerId,
                    'user_name' => '.',
                    'user_phone' => '.',
                    'region' => '.',
                    'district' => '.',
                    'status' => '2',
                    'substatus' => '1',
                    'status_color' => '#05ff00',
                    'is_checking' => false,
                    'order_group_id' => $orderGroup->id,
                ]);

                foreach ($items as $item) {
                    $quantity = $item['quantity'];
                    $basePrice = $item['price'];
                    $finalPrice = $basePrice;

                    // Determine the final price based on quantity ranges
                    if (isset($item['quantityRanges']) && count($item['quantityRanges']) > 0) {
                        $applicableRange = null;

                        foreach ($item['quantityRanges'] as $range) {
                            if (
                                $quantity >= $range['minQuantity']
                            ) {
                                $applicableRange = $range;
                            }
                        }

                        if ($applicableRange) {
                            $finalPrice = $applicableRange['price'];
                        }
                    }

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'title' => $item['title'],
                        'provider' => $item['provider'],
                        'image_url' => $item['image_url'],
                        'quantity' => $quantity,
                        'price' => $finalPrice,
                        'product_url' => $item['product_url'],
                        'skuId' => $item['sku_id'],
                    ]);

                    foreach ($item['properties'] as $property) {
                        Properties::create([
                            "property_id" => $property['id'],
                            "name" => $property['name'],
                            "value" => $property['value'],
                            "order_item_id" => $orderItem->id,
                        ]);
                    }

                    $this->removeBasket($user->id, $item['sku_id']);
                }

                $orders[] = $order;
            }
        });

        Mail::to('optomall.uz@gmail.com')->send(new OrderCreated($orderGroup->id));

        return response()->json(['message' => 'order_created_successfully', 'order_group' => $orderGroup->id], 201);
    }

    private function saveOrderItemImage($client, $url)
    {
        try {
            // Generate a random name for the image file
            $randomName = Str::random(40) . '.' . pathinfo($url, PATHINFO_EXTENSION);
            // Define the path where the image will be stored
            $imagePath = 'order_images/' . $randomName;

            // Use Guzzle to make an HTTP GET request to the image URL
            $response = $client->get($url);

            // Store the image in the public disk (storage/app/public/order_images/)
            Storage::disk('public')->put($imagePath, $response->getBody());

            return $imagePath; // Return the path where the image was stored
        } catch (\Exception $e) {
            // Handle the exception, e.g., log the error and return null
            return null;
        }
    }

    private function removeBasket($userId, $skuId)
    {
        $basketItem = BasketItem::where('user_id', $userId)
            ->where('skuId', $skuId)
            ->first();

        if ($basketItem) {
            $basketItem->delete();
        }
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
        $orderGroup = OrderGroup::where('id', $request->order_id)
            ->where('user_id', auth()->user()->id)
            ->with(['orders.items', 'orders.getStatus']) // Eager load related models
            ->first();

        if (!$orderGroup) {
            return response()->json(['message' => 'Order group not found'], 404);
        }

        $cardNumber = CardNumber::orderBy('money', 'desc')
            ->where('type', 'order')
            ->where('is_active', 1)
            ->first();

        $kgPrice = Exchanges::where('currency', 'kg')->first();

        return response()->json([
            'message' => 'successful',
            'order' => [
                'id' => $orderGroup->id,
                'total_price' => $orderGroup->total_price,
                'kg_price' => $kgPrice->amount ?? 0,
                'card_number' => $cardNumber->card_number ?? '9860176612846848',
                'name' => $cardNumber->name ?? 'JR'
            ],
            'detail' => OrderResource::collection($orderGroup->orders)
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/edit-order",
     *     tags={"Order"},
     *     summary="Edit order by order_id",
     *     description="Edit order details by providing order_id, region, and district",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the order"
     *     ),
     *     @OA\Parameter(
     *         name="region",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="district",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     )
     * )
     */
    public function editOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'region' => 'required|string',
            'district' => 'required|string',
            'name' => 'required|string',
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $user = auth()->user();

        // Check and update user information if region, district, or address is missing
        if (empty($user->region) || empty($user->district)) {
            $user->region = $user->region ?? $request->region;
            $user->district = $user->district ?? $request->district;
            $user->save(); // Save the updated user information
        }

        $orderGroup = OrderGroup::where('id', $request->order_id)->where('user_id', $user->id)
            ->first();
        // return $basketItemSize;

        foreach ($orderGroup->orders as $order) {
            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $order->region = $request->region;
            $order->district = $request->district;
            $order->user_name = $request->name;
            $order->user_phone = $request->phone;
            $order->save();
        }

        return response()->json(['message' => 'Order successfully updated'], 200);
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
     *                 required={"order_group_id", "image", "type"},
     *                 @OA\Property(property="order_group_id", type="integer", example="1"),
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
            'order_group_id' => 'required|integer',
            'image' => 'required|image',
            'type' => 'required|in:order,cargo',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $orderGroup = OrderGroup::find($request->order_group_id);

        if (!$orderGroup) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            // Transaction block
            $result = DB::transaction(function () use ($request, $orderGroup) {
                // Check for OrderCheck limit
                if (OrderCheck::where('order_group_id', $request->order_group_id)->count() > 4) {
                    throw new \Exception('order_checks_limit_reached');
                }

                // Create income record
                Income::create([
                    'user_id' => auth()->user()->id,
                    'order_id' => $request->order_group_id,
                    'amount' => $orderGroup->total_price,
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
                    'order_group_id' => $request->order_group_id,
                    'image' => $request->image->store('order_checks', 'public'), // Store the image and save the path
                    'card_number_id' => $cardNumber->id,
                ]);
                $defaultStatus = OrderStatus::where('for_first', true)->first();
                $statusId = $defaultStatus ? $defaultStatus->id : '3';
                Order::where('order_group_id', $request->order_group_id)->update(['status' => $statusId]);
                // Update order status
                $orderGroup->status = $statusId;
                $orderGroup->save();

                return $orderCheck;
            });

            return response()->json(['message' => 'Order check created successfully', 'order' => $result], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
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
     *                 required={"product_id", "title", "price", "quantityRanges", "quantity", "max_quantity", "image_url", "seller_id", "provider", "sku_id", "properties"},
     *                 @OA\Property(property="product_id", type="string", example="1"),
     *                 @OA\Property(property="title", type="string", example="Product Title"),
     *                 @OA\Property(property="price", type="number", format="float", example="29.99"),
     *                 @OA\Property(property="quantityRanges", type="array", @OA\Items(
     *                     @OA\Property(property="minQuantity", type="integer", example="2"),
     *                     @OA\Property(property="price", type="number", format="float", example="411930")
     *                 )),
     *                 @OA\Property(property="quantity", type="integer", example="2"),
     *                 @OA\Property(property="max_quantity", type="integer", example="5"),
     *                 @OA\Property(property="image_url", type="string", example="http://example.com/image.jpg"),
     *                 @OA\Property(property="product_url", type="string", example="http://example.com/product.jpg"),
     *                 @OA\Property(property="seller_id", type="string", example="22"),
     *                 @OA\Property(property="provider", type="string", example="taobao"),
     *                 @OA\Property(property="sku_id", type="string", example="SKU12345"),
     *                 @OA\Property(property="properties", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="name", type="string", example="Color"),
     *                     @OA\Property(property="value", type="string", example="Red")
     *                 )),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid data"),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     * )
     */
    public function userBasket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.product_id' => 'required|string',
            '*.title' => 'required|string',
            '*.price' => 'required|numeric',
            '*.quantityRanges' => 'nullable|array',
            '*.quantityRanges.*.minQuantity' => 'nullable|integer|min:1',
            '*.quantityRanges.*.price' => 'nullable|numeric',
            '*.quantity' => 'required|integer|min:1',
            '*.max_quantity' => 'required|integer|min:1',
            '*.image_url' => 'required|string',
            '*.product_url' => 'required|string',
            '*.seller_id' => 'required|string',
            '*.provider' => 'required|string',
            '*.sku_id' => 'required|string',
            '*.properties' => 'nullable|array',
            '*.properties.*.id' => 'required|string',
            '*.properties.*.name' => 'required|string',
            '*.properties.*.value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $baskets = [];
        foreach ($request->all() as $item) {
            $quantity = $item['quantity'];
            $basePrice = $item['price'];
            $finalPrice = $basePrice;

            // Determine the final price based on the highest matching quantity range
            if (isset($item['quantityRanges']) && count($item['quantityRanges']) > 0) {
                $applicableRange = null;

                foreach ($item['quantityRanges'] as $range) {
                    if ($quantity >= $range['minQuantity']) {
                        $applicableRange = $range; // This will update with the highest matching minQuantity
                    }
                }

                if ($applicableRange) {
                    $finalPrice = $applicableRange['price'];
                }
            }

            // Check if the basket item with the same sku_id already exists
            $existingBasketItem = BasketItem::where('skuId', $item['sku_id'])
                ->where('user_id', auth()->user()->id)
                ->first();

            if ($existingBasketItem) {
                // Update the quantity if the item exists
                $existingBasketItem->quantity += $quantity;
                $existingBasketItem->price = $finalPrice;
                $existingBasketItem->save();
                $basketItem = $existingBasketItem;
            } else {
                // Create a new basket item if it doesn't exist
                $basketItem = BasketItem::create([
                    'product_id' => $item['product_id'],
                    'user_id' => auth()->user()->id,
                    'title' => $item['title'],
                    'basket_id' => 0, // Adjust as needed
                    'price' => $finalPrice,
                    'quantity' => $quantity,
                    'max_quantity' => $item['max_quantity'],
                    'image_url' => $item['image_url'],
                    'product_url' => $item['product_url'],
                    'seller_id' => $item['seller_id'],
                    'provider' => $item['provider'],
                    'skuId' => $item['sku_id'],
                ]);

                // Save the properties if any
                if (isset($item['properties']) && is_array($item['properties'])) {
                    foreach ($item['properties'] as $property) {
                        Properties::create([
                            'basket_item_id' => $basketItem->id,
                            'property_id' => $property['id'],
                            'name' => $property['name'],
                            'value' => $property['value'],
                        ]);
                    }
                }
            }

            $baskets[] = $basketItem;
        }

        return response()->json(['message' => 'products_added_to_basket', 'baskets' => $baskets], 201);
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

        $basketItems = BasketItem::where('user_id', $user->id)->get();
        // return $basketItems;
        if ($basketItems->isEmpty()) {
            return response()->json([], 200);
        }

        // Return the filtered baskets as a JSON response using the BasketResource collection
        return response()->json(BasketResource::collection($basketItems), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/has-items",
     *     tags={"Order"},
     *     summary="Has items in the basket",
     *     security={{ "bearerAuth":{} }},
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
    public function hasItems(Request $request)
    {
        $user = $request->user();

        $basketItems = BasketItem::where('user_id', $user->id)->get();

        $boolean = false;

        if ($basketItems->isEmpty()) {
            $boolean = false;
        } else {
            $boolean = true;
        }

        // Return the filtered baskets as a JSON response using the BasketResource collection
        return response()->json($boolean, 200);
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
                    'image_url' => $order->items->first()->image_url,
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
                    'image_url' => $order->items->first()->image_url,
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
     *             required={"basket_id", "quantity"},
     *             @OA\Property(property="basket_id", type="integer", example="1"),
     *             @OA\Property(property="quantity", type="integer", example="2"),
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
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $basket = Basket::where('id', $request->basket_id)
            ->first();
        // return $basketItemSize;

        if (!$basket) {
            return response()->json(['message' => 'Basket item size not found'], 404);
        }

        $basket->quantity = $request->quantity;
        $basket->save();

        return response()->json(['message' => 'size_quantity_updated_successfully', 'basket_item_size' => $basket], 200);
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
     *             required={"basket_id"},
     *             @OA\Property(property="basket_id", type="integer", example="1"),
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
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        // Find the basket item size by basket_id and size_id
        $basket = BasketItem::where('id', $request->basket_id)->where('user_id', auth()->user()->id)->first();

        if (!$basket) {
            return response()->json(['message' => 'Basket item not found'], 404);
        }

        // Remove the size and associated colors from the basket
        $basket->delete();

        return response()->json($this->getUserBasket($request), 200);
    }
}
