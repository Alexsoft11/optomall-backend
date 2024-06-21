<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
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
            'items.*.composition_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid_data', 'errors' => $validator->errors(), 'error' => true], 400);
        }

        $order = DB::transaction(function () use ($request) {
            $totalPrice = 0;

            foreach ($request->items as $item) {
                // Fetch product details using productDetailFromTaoBao method
                $productDetailResponse = $this->productController->productDetailFromTaoBao(new Request(['productId' => $item['product_id']]));
                // dd($item['productDetailResponse']);
                $productDetail = json_decode($productDetailResponse->getContent(), true)['detail'];
                $totalPrice += $item['quantity'] * $productDetail['price'];
            }

            $order = Order::create([
                'user_id' => auth()->user()->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                // Fetch product details again for each item
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
        $orders = Order::where('user_id', $user->id)->where('status', 'delivered')->get();
        return response()->json(['orders' => $orders], 200);
    }
}
