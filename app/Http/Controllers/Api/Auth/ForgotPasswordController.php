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
            'type' => 'required|in:email,phone',
            'email' => 'required_if:type,email|string|email|max:255',
            'phone_number' => 'required_if:type,phone|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'validation_failed', 'error' => true, 'errors' => $validator->errors()], 422);
        }

        if ($request->type == 'email') {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $user->notify(new ResetPasswordNotification());
            } else {
                return response()->json(['message' => 'user_not_found', 'error' => true], 404);
            }
        } elseif ($request->type == 'phone') {
            $user = User::where('phone', $request->phone_number)->first();
            if ($user) {
                $request->replace(['phone_number' => $user->phone]);
                return $this->sendOTP->sendOTP($request);
            } else {
                return response()->json(['message' => 'user_not_found', 'error' => true], 404);
            }
        }

        return response()->json(['message' => 'success'], 200);
    }
}
