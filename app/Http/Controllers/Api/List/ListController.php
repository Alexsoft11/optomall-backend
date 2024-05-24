<?php

namespace App\Http\Controllers\Api\List;

use App\Models\Social;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Socials;
use OpenApi\Annotations as OA;

class ListController extends Controller
{
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

    $socials = Socials::all();
    return response()->json(['socials' => $socials], 200);
}

    }
