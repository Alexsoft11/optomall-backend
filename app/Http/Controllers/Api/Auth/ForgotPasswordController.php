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
    protected $sendOTP;

    public function __construct()
    {
        $this->sendOTP = new \App\Http\Controllers\Api\Auth\AuthController();
    }

    /**
     * @OA\Post(
     *     path="/api/password/forgot",
     *     summary="Initiate password reset",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="string", example="khamidovsherzod003@gmail.com")
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
            'email' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid_email_format', 'error' => true], 422);
        }

        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->email)->first();

            if ($user) {
                $user->notify(new ResetPasswordNotification());
            } else {
                return response()->json(['message' => 'user_not_found', 'error' => true], 404);
            }
        } else {
            $user = User::where('phone', $request->email)->first();
            if ($user) {
                $request->replace(['phone_number' => $user->phone]);
                return $this->sendOTP->sendOtp($request);
            } else {
                return response()->json(['message' => 'user_not_found', 'error' => true], 404);
            }
        }

        return response()->json(['message' => 'success'], 200);
    }
}
