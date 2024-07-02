<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\BasketItemColor;
use App\Models\BasketItemSize;
use App\Models\Order;
use App\Models\OrderItem;
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
     *             required={"items"},
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer", example="1"),
     *                 @OA\Property(property="quantity", type="integer", example="2"),
     *                 @OA\Property(property="color_id", type="integer", example="3"),
     *                 @OA\Property(property="size_id", type="integer", example="4"),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="order_id", type="integer", example="1"),
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
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.color_id' => 'required|integer|min:1',
            'items.*.size_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid_data', 'errors' => $validator->errors(), 'error' => true], 400);
        }

        $order = DB::transaction(function () use ($request) {
            $totalPrice = 0;

            foreach ($request->items as $item) {
                $productDetailResponse = $this->productController->productDetailFromTaoBao(new Request(['productId' => $item['product_id']]));
                $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];
                $totalPrice += $item['quantity'] * $productDetail['price'];
            }

            $order = Order::create([
                'user_id' => auth()->user()->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                $productDetailResponse = $this->productController->productDetailFromTaoBao(new Request(['productId' => $item['product_id']]));
                $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $productDetail['price'],
                    'color_id' => $item['color_id'],
                    'size_id' => $item['size_id'],
                ]);
            }

            return $order;
        });

        return response()->json(['message' => 'order_created_successfully', 'order' => $order], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/user-basket",
     *     tags={"Order"},
     *     summary="Create a new Basket",
     *     description="Create a new Basket with items",
     *     operationId="createBasket",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Basket details",
     *         @OA\JsonContent(
     *             required={"product_id", "sizes", "colors"},
     *             @OA\Property(property="product_id", type="integer", example="1"),
     *             @OA\Property(property="sizes", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example="4"),
     *                 @OA\Property(property="quantity", type="integer", example="2"),
     *             )),
     *             @OA\Property(property="colors", type="array", @OA\Items(type="integer", example=1)),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Basket created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Basket created successfully"),
     *             @OA\Property(property="order_id", type="integer", example="1"),
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
            'product_id' => 'required|integer|min:1',
            'sizes' => 'required|array',
            'sizes.*.id' => 'required|integer|min:1',
            'sizes.*.quantity' => 'required|integer|min:1',
            'colors' => 'required|array',
            'colors.*' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $basket = Basket::create([
            'user_id' => auth()->user()->id,
            'product_id' => $request->product_id,
        ]);

        foreach ($request->sizes as $size) {
            BasketItemSize::create([
                'basket_id' => $basket['id'],
                'size_id' => $size['id'],
                'quantity' => $size['quantity'],
            ]);
        }

        foreach ($request->colors as $color) {
            BasketItemColor::create([
                'basket_id' => $basket['id'],
                'color_id' => $color,
            ]);
        }

        return response()->json(['message' => 'Product added to basket', 'basket' => $basket], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/get-orders",
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
    public function getOrders(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)->get();
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
        $baskets = Basket::where('user_id', $user->id)->with('colors', 'sizes')->get();

        $result = [];

        foreach ($baskets as $basket) {
            // Simulating product details fetch
            $productDetailResponse = $this->productController->productDetailFromTaoBao(new Request(['productId' => $basket['product_id']]));
            $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];

            $basketColorIds = $basket->colors->pluck('color_id')->toArray();
            $basketSizeIds = $basket->sizes->pluck('size_id')->toArray();

            $productColors = collect($productDetail['colors'])->whereIn('id', $basketColorIds)->values();
            $productSizes = collect($productDetail['sizes'])->whereIn('id', $basketSizeIds)->values();

            foreach ($basket->sizes as $basketSize) {
                foreach ($basket->colors as $basketColor) {
                    $sizeDetail = $productSizes->firstWhere('id', $basketSize->size_id);
                    $colorDetail = $productColors->firstWhere('id', $basketColor->color_id);

                    $result[] = [
                        'id' => $productDetail['id'] ?? null,
                        'basket_id' => $basket['id'] ?? null,
                        'title' => $productDetail['title'] ?? null,
                        'size' => $sizeDetail['value'] ?? null,
                        'size_id' => $sizeDetail['id'] ?? null,
                        'color' => $colorDetail['value'] ?? null,
                        'color_id' => $colorDetail['id'] ?? null,
                        'quantity' => $basketSize['quantity'] ?? 1,
                        'image_url' => $colorDetail['image_url'] ?? null,
                        'price' => $productDetail['price'] ?? null
                    ];
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
        $orders = Order::where('user_id', $user->id)->where('status', 'delivered')->where('type', 'order')->get();
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
            'size_id' => 'required|integer|exists:basket_item_sizes,size_id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $basketItemSize = BasketItemSize::where('basket_id', $request->basket_id)
            ->where('size_id', $request->size_id)
            ->first();
        // return $basketItemSize;

        if (!$basketItemSize) {
            return response()->json(['message' => 'Basket item size not found'], 404);
        }

        $basketItemSize->quantity = $request->quantity;
        $basketItemSize->save();

        return response()->json(['message' => 'Size quantity updated successfully', 'basket_item_size' => $basketItemSize], 200);
    }
}
