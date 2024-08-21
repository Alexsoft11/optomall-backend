<?php

namespace App\Http\Controllers\Api\Slider;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Traits\ApiResponseHelpers;
use Illuminate\Http\Request;
use App\Http\Resources\SliderResource;
use Illuminate\Support\Facades\Cache;

class SliderController extends Controller
{

    use ApiResponseHelpers;

    /**
     * @OA\Get(
     *     path="/api/sliders",
     *     summary="Get list of sliders",
     *     tags={"Slider"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $cacheKey = 'sliders_list'; // Define a unique key for caching

        // Retrieve sliders from the cache or fetch from the database if not cached
        $sliders = Cache::remember($cacheKey, 300, function () {
            return Slider::all(); // Fetch all sliders from the database
        });

        // Return the response with cached or newly fetched sliders
        return $this->respondWithSuccess([
            'sliders' => SliderResource::collection($sliders),
        ]);
    }
}
