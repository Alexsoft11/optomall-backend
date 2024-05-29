<?php

namespace App\Http\Controllers\Api\List;

use Illuminate\Http\Request;
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
}
