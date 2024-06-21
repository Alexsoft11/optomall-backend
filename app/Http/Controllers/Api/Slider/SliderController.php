<?php

namespace App\Http\Controllers\Api\Slider;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Traits\ApiResponseHelpers;
use Illuminate\Http\Request;
use App\Http\Resources\SliderResource;

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
        $sliders = Slider::all();

        return $this->respondWithSuccess([
            'sliders' => SliderResource::collection($sliders)
        ]);
    }
}
