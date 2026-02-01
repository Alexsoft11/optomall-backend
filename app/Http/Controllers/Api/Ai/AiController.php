<?php

namespace App\Http\Controllers\Api\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiSearchHistory;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\ProductTokenResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class AiController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * @OA\Post(
     *     path="/api/ai/init",
     *     summary="Initialize AI Search",
     *     tags={"AI"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="prompt", type="string"),
     *             @OA\Property(property="provider", type="string", example="1688"),
     *             @OA\Property(property="model", type="string", example="openai")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Returns Search ID")
     * )
     */
    public function init(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string',
            'provider' => 'nullable|string|in:1688,taobao',
            'model' => 'nullable|string|in:openai,deepseek',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        Log::channel('ai')->info("AI Init Request", ['user_id' => $request->user()?->id, 'prompt' => $request->prompt, 'model' => $request->model]);

        $result = $this->aiService->generateSearchKeys($request->prompt, $request->model ?? 'openai');

        // Handle unexpected structure or error array
        $keywords = $result['keywords'] ?? [];
        $usage = $result['usage'] ?? [];

        // If fallback triggered, structure might be simple array if I missed something, but I standardized it.
        // Ensure keywords is array
        if (!is_array($keywords)) {
            $keywords = [$keywords];
        }

        $history = AiSearchHistory::create([
            'user_id' => $request->user() ? $request->user()->id : null,
            'prompt' => $request->prompt,
            'generated_keywords' => $keywords,
            'items_provider' => $request->provider ?? '1688',
            'ai_model' => $request->model ?? 'openai',
            'input_tokens' => $usage['input_tokens'] ?? null,
            'output_tokens' => $usage['output_tokens'] ?? null,
            'total_tokens' => $usage['total_tokens'] ?? null,
        ]);

        return response()->json(['id' => $history->id, 'keywords' => $keywords]);
    }

    /**
     * @OA\Post(
     *     path="/api/ai/search",
     *     summary="Perform AI Search",
     *     tags={"AI"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="page", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Returns Mixed Search Results")
     * )
     */
    public function search(Request $request)
    {
        $history = AiSearchHistory::find($request->id);
        if (!$history) {
            return response()->json(['error' => 'Not found'], 404);
        }

        Log::channel('ai')->info("AI Search Execution", ['history_id' => $history->id, 'keywords' => $history->generated_keywords]);

        $keywords = $history->generated_keywords;
        if (empty($keywords)) {
            $keywords = [$history->prompt];
        }

        // Limit to 3 keywords
        $keywords = array_slice($keywords, 0, 3);

        $results = [];
        $promises = [];

        $page = $request->page ?? 1;
        $pageSize = 20; // smaller page size per request since we mix

        // Parallel requests would be ideal, but Laravel sync execution is easier to implement first.
        // For true parallel execution in PHP we might need Guzzle Promises or similar.
        // Using Http::pool if available (Laravel 8+)

        $responses = Http::pool(
            fn($pool) =>
            collect($keywords)->map(function ($keyword) use ($pool, $history, $page, $pageSize) {
                // Construct URL similar to ProductController
                $lang = 'en'; // default
                $url = "https://api.kongcrdv.com/taobao/api_call.php?api_name=item_search&lang=$lang&q=$keyword&page=$page&page_size=$pageSize&key=kingdev5809@gmail.com";
                return $pool->get($url);
            })
        );

        foreach ($responses as $response) {
            if ($response instanceof \Illuminate\Http\Client\Response && $response->ok()) {
                $data = $response->json();
                if (isset($data['items']['item'])) {
                    $results[] = $data['items']['item'];
                }
            }
        }

        // Mix results
        // Strategy: Take 1st from A, 1st from B, 1st from C, 2nd from A...
        $mixed = [];
        $maxCount = 0;
        foreach ($results as $list) {
            $maxCount = max($maxCount, count($list));
        }

        for ($i = 0; $i < $maxCount; $i++) {
            foreach ($results as $list) {
                if (isset($list[$i])) {
                    // Check for duplicates by num_iid
                    $exists = false;
                    foreach ($mixed as $m) {
                        if (isset($m['num_iid']) && isset($list[$i]['num_iid']) && $m['num_iid'] == $list[$i]['num_iid']) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $mixed[] = $list[$i];
                    }
                }
            }
        }

        return response()->json([
            'data' => ProductTokenResource::collection($mixed),
            'meta' => [
                'keywords' => $keywords
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/ai/feedback",
     *     summary="Submit Feedback",
     *     tags={"AI"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="like", type="boolean"),
     *             @OA\Property(property="comment", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Feedback Saved")
     * )
     */
    public function feedback(Request $request)
    {
        $history = AiSearchHistory::find($request->id);
        if (!$history) {
            return response()->json(['error' => 'Not found'], 404);
        }

        Log::channel('ai')->info("AI Feedback", ['id' => $request->id, 'like' => $request->like, 'comment' => $request->comment]);

        if ($request->has('like')) {
            $history->feedback_like = $request->like;
        }
        if ($request->has('comment')) {
            $history->feedback_comment = $request->comment;
        }
        $history->save();

        return response()->json(['success' => true]);
    }
    /**
     * @OA\Get(
     *     path="/api/ai/history",
     *     summary="Get AI Search History",
     *     tags={"AI"},
     *     @OA\Response(response=200, description="Returns History")
     * )
     */
    public function history(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['data' => []]);
        }

        $history = AiSearchHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'prompt' => $item->prompt,
                    'keywords' => $item->generated_keywords,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json(['data' => $history]);
    }
}
