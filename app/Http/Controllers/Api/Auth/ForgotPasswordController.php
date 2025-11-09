<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ForgotPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Resend;

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
            'phone_number' => 'required_if:type,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'validation_failed', 'errors' => $validator->errors()], 422);
        }

        if ($request->type === 'email') {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['message' => 'email_doesnt_exist'], 404);
            }

            $otp = (new Otp())->generate($user->email, 'numeric', 6, 15);

            try {
                $resend = Resend::client(env('RESEND_API_KEY'));

                $resend->emails->send([
                    'from' => 'Optomall <noreply@optomall.uz>',
                    'to' => [$user->email],
                    'subject' => 'Parolni tiklash kodi',
                    'html' => '<p>Parolingizni tiklash uchun quyidagi koddan foydalaning:</p>'
                        . '<h2 style="color: #333;">' . $otp->token . '</h2>'
                        . '<p>Kod 15 daqiqa davomida amal qiladi.</p>',
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'email_sending_failed',
                    'error' => $e->getMessage(),
                ], 400);
            }
        } elseif ($request->type === 'phone') {
            $user = User::where('phone', $request->phone_number)->first();
            if (!$user) {
                return response()->json(['message' => 'phone_doesnt_exist'], 404);
            }

            $request->replace(['phone_number' => $user->phone]);
            return $this->sendOTP->sendForgotOTP($request);
        }

        return response()->json(['message' => 'success'], 200);
    }
}
