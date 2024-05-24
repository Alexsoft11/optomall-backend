<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class ProfileController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/profile/update",
     *     summary="Update user profile",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "last_name", "phone"},
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone", type="string", example="+1234567890")
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Profile updated successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user = Auth::user();
        $user->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
        ]);

        return response()->json(['message' => 'Profile updated successfully'], 200);
    }
}

