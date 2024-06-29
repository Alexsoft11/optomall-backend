<?php

namespace App\Http\Controllers\Api\List;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Questions;
use App\Models\Comment;
use App\Models\Product;
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
        $questions = Questions::with('answer_id, question_id, name_uz, name_ru, name_en')->get(['id', 'name_uz, name_ru, name_en']);

        $items = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'name_uz' => $question->name_uz,
                'name_ru' => $question->name_ru,
                'name_en' => $question->name_en,
                'answers' => $question->answers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
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
 *         @OA\JsonContent(
 *             required={"product_id", "name"},
 *             @OA\Property(property="product_id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Great product!")
 *         )
 *     ),
 *     @OA\Response(response="200", description="Comment posted successfully"),
 *     @OA\Response(response="400", description="Invalid input"),
 *     @OA\Response(response="401", description="Unauthorized"),
 *     @OA\Response(response="404", description="Product not found"),
 * )
 */
public function postComment(Request $request)
{
    $request->validate([
        'product_id' => 'required|integer',
        'name' => 'required|string',
    ]);

    $user = $request->user();
    $product_id = $request->product_id;
    $subCategoryId = $request->subCategoryId;
    $productsCount = $request->productsCount ? $request->productsCount : 20;

    $product = Product::find($product_id);

    if (!$product) {
        $response = Http::get("http://otapi.net/service-json/BatchSearchItemsFrame?instanceKey={$this->apiKey}&language=uz&signature=ede6e796a3f325d3d0a7743c7c4e7d93648c74d7c41744f1671714e327b5d52b&timestamp=20240526085107&sessionId=&xmlParameters=%3CSearchItemsParameters%3E%3CCategoryId%3E$subCategoryId%3C%2FCategoryId%3E%3C%2FSearchItemsParameters%3E&framePosition=0&frameSize=$productsCount&blockList=/{$product_id}");

        if ($response->failed()) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $productData = $response->json();
    }

    $comment = new Comment();
    $comment->name = $request->name;
    $comment->user_id = $user->id;
    $comment->product_id = $product_id; 
    $comment->save();

    return response()->json(['message' => 'Comment posted successfully'], 200);
}

}
