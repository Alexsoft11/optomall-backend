<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\BasketItemColor;
use App\Models\BasketItemSize;
use App\Models\CardNumber;
use App\Models\Order;
use App\Models\OrderCheck;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
     *                 @OA\Property(property="name", type="string", example="Uzbekistan"),
     *                 @OA\Property(property="url", type="string", example="https://google.com")
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
            'location.name' => 'required|string',
            'location.url' => 'required|string',
            'items.*.product_id' => 'required|string',
            'items.*.title' => 'required|string',
            'items.*.image_url' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.color' => 'required|string',
            'items.*.size' => 'required|string',
            // 'items.*.size_id' => 'required|integer',
            'items.*.price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid_data', 'errors' => $validator->errors(), 'error' => true], 400);
        }

        $order = DB::transaction(function () use ($request) {
            $order = Order::create([
                'user_id' => auth()->user()->id,
                'total_price' => $request->total_price,
                'user_name' => $request->userData['name'],
                'user_phone' => $request->userData['phone'],
                'location_url' => $request->location['url'],
                'location_name' => $request->location['name'],
                'status' => 'processing',
                'status_color' => 'black',
                'is_checking' => false
            ]);

            foreach ($request->items as $item) {
                $productDetailResponse = $this->productController->productDetailFromTaoBao(new Request(['productId' => $item['product_id']]));
                $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'title' => $item['title'],
                    'image_url' => $item['image_url'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'color' => $item['color'],
                    'size' => $item['size'],
                    'product_url' => $productDetail['product_url'],
                ]);

                // // Remove basket items associated with the size_id and color_id
                // $basketItemSize = BasketItemSize::where('user_id', auth()->user()->id)
                //     ->where('id', $item['size_id'])
                //     ->first();

                // if ($basketItemSize) {
                //     $basketId = $basketItemSize->basket_id;
                //     $basketItemSize->delete();

                //     // Remove associated BasketItemColor
                //     $basketItemColor = BasketItemColor::where('basket_item_size_id', $item['size_id'])
                //         ->where('color_id', $basketItemSize['color_id'])
                //         ->first();

                //     if ($basketItemColor) {
                //         $basketItemColor->delete();
                //     }

                //     // Check if the basket has no more sizes or colors, then delete the basket
                //     if (
                //         !BasketItemSize::where('basket_id', $basketId)->exists() &&
                //         !BasketItemColor::where('basket_item_size_id', $item['size_id'])->exists()
                //     ) {
                //         Basket::where('id', $basketId)->delete();
                //     }
                // }
            }

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
        $cardNumber = CardNumber::orderBy('money', 'desc')->first();

        return response()->json(['message' => 'successfull', 'order' => ['id' => $order->id, 'total_price' => $order->total_price, 'card_number' => $cardNumber->card_number]], 201);
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
     *                 required={"order_id", "image"},
     *                 @OA\Property(property="order_id", type="integer", example="1"),
     *                 @OA\Property(property="image", type="string", format="binary")
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
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'image' => 'required|image'
        ]);

        if (OrderCheck::where('order_id', $request->order_id)->count() > 4) {
            return response()->json(['message' => 'Order checks limit'], 400);
        }

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $order = DB::transaction(function () use ($request) {
            $cardNumber = CardNumber::orderBy('money', 'desc')->first();
            $order = OrderCheck::create([
                'order_id' => $request->order_id,
                'image' => $request->image->store('order_checks', 'public'), // Store the image and save the path
                'card_number_id' => $cardNumber->id
            ]);

            return $order;
        });

        return response()->json(['message' => 'Order check created successfully', 'order' => $order], 201);
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

        $filter = $request->query('filter', 'all');

        $verificationStatuses = OrderStatus::where('type', 'in_verification')->pluck('slug');
        $activeStatuses = OrderStatus::where('type', 'active')->pluck('slug');

        $ordersQuery = Order::where('user_id', $user->id)->select('id', 'status', 'total_price', 'user_name', 'user_phone', 'location_url', 'location_name', 'created_at');

        if ($filter === 'in_verification') {
            $ordersQuery->whereIn('status', $verificationStatuses);
        } elseif ($filter === 'active') {
            $ordersQuery->whereIn('status', $activeStatuses);
        }

        $orders = $ordersQuery->with(['items' => function ($query) {
            $query->select('id', 'order_id', 'title', 'price', 'image_url');
        }])->get();


        $orders = $orders->map(function ($order) {
            $order->items = $order->items->map(function ($item) {
                return [
                    'title' => $item->title,
                    'price' => $item->price,
                    'image_url' => $item->image_url,
                ];
            });
            return $order;
        });

        return response()->json(['orders' => $orders], 200);
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
        $baskets = Basket::where('user_id', $user->id)->with('sizes')->get();
        $result = [];

        foreach ($baskets as $basket) {
            // Simulating product details fetch
            $productDetailResponse = $this->productController->productDetailFromTaoBao(new Request(['productId' => $basket['product_id']]));
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
     *     path="/api/success-orders",
     *     tags={"Order"},
     *     summary="Get orders for authenticated user",
     *     description="Retrieve orders created by the authenticated user",
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
    public function getSuccessOrders(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)->get();
        return response()->json(['orders' => $orders], 200);
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
