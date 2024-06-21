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
     *     path="/api/profile/update-email",
     *     summary="Update user email",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email" , example="admin@mail.com"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Profile updated successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = Auth::user();
        $user->update([
            'email' => $request->email,
        ]);

        return response()->json(['message' => 'Email updated successfully'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/profile/update-name",
     *     summary="Update user name",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Abdurashidbek"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Profile updated successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function updateName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = Auth::user();
        $user->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'name updated successfully'], 200);
    }

     /**
     * @OA\Post(
     *     path="/api/profile/update-phone",
     *     summary="Update user phone",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="+9981234557"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Profile updated successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function updatePhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = Auth::user();
        $user->update([
            'phone' => $request->phone,
        ]);

        return response()->json(['message' => 'phone updated successfully'], 200);
    }


         /**
     * @OA\Post(
     *     path="/api/profile/update-password",
     *     summary="Update user password",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password" , "confirm_password"},
     *             @OA\Property(property="password", type="string", example="new-password"),
     *             @OA\Property(property="confirm_password", type="string", example="new-password"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Profile updated successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|max:255',
            'confirm_password' => 'required|string|max:255|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = Auth::user();
        $user->update([
            'password' => $request->password,
            'confirm_password' => $request->confirm_password,
        ]);

        return response()->json(['message' => 'Password updated successfully'], 200);
    }
}
