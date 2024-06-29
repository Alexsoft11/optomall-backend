<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ForgotPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;


class ForgotPasswordController extends Controller
{
/**
 * @OA\Post(
 *     path="/api/password/forgot",
 *     summary="Initiate password reset",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email"},
 *             @OA\Property(property="email", type="string", format="email", example="khamidovsherzod003@gmail.com")
 *         ),
 *     ),
 *     @OA\Response(response="200", description="Success"),
 *     @OA\Response(response="422", description="Validation errors"),
 *     @OA\Response(response="404", description="User not found")
 * )
 */
public function forgotPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'invalid_email_format', 'error' => true], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'user_not_found', 'error' => true], 404);
    }

    $user->notify(new ResetPasswordNotification());

    return response()->json(['message' => "success"], 200);
}


}
