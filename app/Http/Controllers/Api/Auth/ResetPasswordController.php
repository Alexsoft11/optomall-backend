<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ResetPasswordRequest;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;


class ResetPasswordController extends Controller
{
 /**
 * @OA\Post(
 *     path="/api/password/reset",
 *     summary="Reset password",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "otp", "password", "confirm_password"},
 *             @OA\Property(property="email", type="string", format="email", example="khamidovsherzod003@gmail.com"),
 *             @OA\Property(property="otp", type="string", example="123456"),
 *             @OA\Property(property="password", type="string", format="password", example="new-password"),
 *             @OA\Property(property="confirm_password", type="string", format="password", example="new-password")
 *         ),
 *     ),
 *     @OA\Response(response="200", description="Password reset successful"),
 *     @OA\Response(response="401", description="User not found"),
 *     @OA\Response(response="422", description="Validation errors")
 * )
 */
private $otp;

public function __construct(){
    $this->otp = new Otp();
}

public function resetPassword(ResetPasswordRequest $request){
    $otp2 = $this->otp->validate($request->email, $request->otp);
    if (!$otp2->status) {
        return response()->json(['error' => 'Invalid OTP'], 401);
    }

    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email',
        'otp' => 'required|string',
        'password' => 'required|string|min:8|same:confirm_password',
        'confirm_password' => 'required|string|min:8',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    $user->update(['password' => Hash::make($request->password)]);
    $user->tokens()->delete();

    $success['success'] = true;
    return response()->json($success, 200);
}

}
