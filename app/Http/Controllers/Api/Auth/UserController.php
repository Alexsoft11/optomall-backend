<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/user-get-data",
 *     summary="Get authenticated user's data",
 *     tags={"User"},
 *     security={{ "bearerAuth":{} }},
 *     @OA\Response(response="200", description="User's data"),
 *     @OA\Response(response="401", description="Unauthenticated"),
 * )
 */
public function user(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    return response()->json([
        'name' => $user->name,
        'unique_id' => $user->unique_id,
        'email' => $user->email,
        'last_name' => $user->last_name,
        'phone' => $user->phone,
    ], 200);
}
}
