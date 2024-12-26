<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Models\TempUser;
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
     *             @OA\Property(property="email", type="string", format="string", example="khamidovsherzod003@gmail.com"),
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

    public function __construct()
    {
        $this->otp = new Otp();
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|same:confirm_password',
            'confirm_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'validation_failed', 'error' => true, 'errors' => $validator->errors()], 422);
        }

        $user = null;

        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->email)->first();
            $user->update(['password' => Hash::make($request->password)]);
            $user->tokens()->delete();
            $otp2 = $this->otp->validate($request->email, $request->otp);
            if (!$otp2->status) {
                return response()->json(['message' => 'invalid_otp', 'error' => true], 401);  // Invalid OTP
            }
        } else {
            $user = User::where('phone', $request->email)->first();
            $user->update(['password' => Hash::make($request->password)]);
            $user->tokens()->delete();
            return $this->verifyOTP($request->otp, $request->email);
        }

        if (!$user) {
            return response()->json(['message' => 'user_not_found', 'error' => true], 404);  // User not found
        }


        return response()->json(['message' => 'password_reset_successful'], 200);  // Password reset successful
    }

    public function verifyOTP($otp, $phone)
    {
        $tempUser = TempUser::where('phone_number', $phone)->first();
        if ($otp == $tempUser->otp) {
            // return 'truee';
            return response()->json(['message' => 'password_reset_successful.']);
        }

        $tempUser->delete();

        return response()->json(['message' => 'invalid_otp.'], 401);
    }
}
