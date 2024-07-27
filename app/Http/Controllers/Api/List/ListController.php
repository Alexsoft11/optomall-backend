<?php

namespace App\Http\Controllers\Api\List;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Socials;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Http;

class ListController extends Controller
{

    private $apiKey;

    public function __construct()
    {
        $this->apiKey = "8e6d9b54-88a7-4dcc-9f2b-add4d68dbbb8";
    }

    /**
     * @OA\Get(
     *     path="/api/socials",
     *     summary="Get a list of socials",
     *     tags={"List"},
     *     @OA\Response(response="200", description="List of socials"),
     * )
     */
    public function socials(Request $request)
    {
        $socials = Socials::select('id', 'name', 'icon', 'path')->get();

        $items = $socials->map(function ($social) {
            return [
                'id' => $social->id,
                'name' => $social->name,
                'path' => $social->path,
                'icon' => $social->icon_url,
            ];
        });

        return response()->json(['socials' => $items], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/questions",
     *     summary="Get a list of questions with their answers",
     *     tags={"List"},
     *     @OA\Response(response="200", description="List of questions with answers"),
     * )
     */
    public function questions(Request $request)
    {
        $questions = Question::with('answers')->get();

        $items = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'name_uz' => $question->name_uz,
                'name_ru' => $question->name_ru,
                'name_en' => $question->name_en,
                'answers' => $question->answers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'question_id' => $answer->question_id,
                        'name_uz' => $answer->name_uz,
                        'name_ru' => $answer->name_ru,
                        'name_en' => $answer->name_en,
                    ];
                }),
            ];
        });

        return response()->json(['questions' => $items], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/post-comment",
     *     summary="Post a comment on a product",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"order_id", "name", "rating"},
     *                 @OA\Property(property="order_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Great product!"),
     *                 @OA\Property(property="rating", type="integer", example=5),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Comment posted successfully"),
     *     @OA\Response(response="400", description="Invalid input"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function postComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'name' => 'required|string',
            'rating' => 'required|integer',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $existingComment = Comment::where('user_id', $user->id)
            ->where('order_id', $request->order_id)
            ->first();

        if ($existingComment) {
            return response()->json(['message' => 'you_wrote_comment'], 400);
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('comments', 'public');
                $imagePaths[] = $path;
            }
        }

        $order = Order::with('items')->find($request->order_id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $product = $order->items->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found in the order'], 404);
        }

        $comment = new Comment();
        $comment->name = $request->name;
        $comment->user_id = $user->id;
        $comment->order_id = $request->order_id;
        $comment->product_id = $product->product_id; 
        $comment->rating = $request->rating;
        $comment->images = $imagePaths;
        $comment->save();

        $uncommentedOrders = Order::where('user_id', $user->id)
            ->whereDoesntHave('comments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'image_url' => url('storage/' . $order->items->first()->image_url),
                    'name' => $order->items->first()->title,
                    'date' => $order->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'message' => 'Success',
            'data' => $uncommentedOrders
        ], 201);
    }



    /**
     * @OA\Get(
     *     path="/api/get-comments",
     *     summary="Get comments for a product",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=true,
     *         description="ID of the product to retrieve comments for",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response="200", description="List of comments retrieved successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="rating", type="integer"),
     *                  @OA\Property(property="images", type="array",
     *                      @OA\Items(type="string", format="url")
     *                  ),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time")
     *              )
     *          )
     *     ),
     *     @OA\Response(response="400", description="Invalid input"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Product not found"),
     * )
     */
    public function getComments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid_input', 'errors' => $validator->errors()], 400);
        }

        $product_id = $request->input('product_id');
        $comments = Comment::with('user')->where('product_id', $product_id)->get();

        if ($comments->isEmpty()) {
            return response()->json(['message' => 'not_found'], 404);
        }

        $comments = $comments->map(function ($comment) {
            $comment->images = array_map(function ($path) {
                return asset('storage/' . $path);
            }, $comment->images);

            return [
                'id' => $comment->id,
                'product_id' => $comment->product_id,
                'user_name' => $comment->user ? $comment->user->name : null,
                'name' => $comment->name,
                'rating' => $comment->rating,
                'images' => $comment->images,
                'created_at' => $comment->created_at->format('d.m.y'),
            ];
        });

        return response()->json($comments, 200);
    }
}
