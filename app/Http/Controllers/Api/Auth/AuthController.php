<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\TempUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Mail\GoogleLoginSuccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

session_start();
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password", "confirm_password"},
     *             @OA\Property(property="email", type="string", format="email", example="khamidovsherzod003@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *             @OA\Property(property="confirm_password", type="string", format="password", example="12345678")
     *         ),
     *     ),
     *     @OA\Response(response="201", description="User registered successfully"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8|same:confirm_password',
            'confirm_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'validation_errors', 'errors' => $validator->errors()], 422);
        }

        $tempUser = TempUser::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'confirm_password' => $request->confirm_password,
        ]);

        $token = Str::random(60);
        Cache::put($token, $tempUser->id, now()->addMinutes(60));

        try {
            $tempUser->notify(new VerifyEmailNotification($token, $tempUser->id));
        } catch (TransportExceptionInterface $e) {
            return response()->json([
                'message' => 'email_sending_failed',
                'error' => 'not_found',
            ], 400);
        }

        return response()->json(['message' => 'verification_email_sent'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/verify-email/{token}",
     *     summary="Verify email and move user to users table if email and password match",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Email verified and user moved to users table"),
     *     @OA\Response(response="404", description="Invalid or expired token")
     * )
     */
    public function verifyEmail($token)
    {
        $tempUserId = Cache::get($token);

        if (!$tempUserId) {
            return response()->json(['message' => 'invalid_or_expired_token'], 404);
        }

        $tempUser = TempUser::find($tempUserId);
        if ($tempUser) {
            $user = User::create([
                'email' => $tempUser->email,
                'password' => $tempUser->password,
                'email_verified' => true,
                'email_verified_at' => now(),
            ]);

            $user->email_verified = true;
            $user->email_verified_at = now();
            $user->save();

            $tempUser->delete();
            Cache::forget($token);

            if (!$user->unique_id) {
                $user->unique_id = $user->id;
                $user->save();
            }

            $redirectUrl = "https://optomall.uz/auth?step=info&id={$user->unique_id}";

            return redirect($redirectUrl);
        }

        return response()->json(['message' => 'user_not_found'], 404);
    }



    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login a user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="string", example="khamidovsherzod003@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678")
     *         ),
     *     ),
     *     @OA\Response(response="200", description="User logged in successfully"),
     *     @OA\Response(response="401", description="Incorrect login or password"),
     *     @OA\Response(response="403", description="Email not verified")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string|min:8',
            'type' => 'nullable|string|in:email,phone', // Ensure type is either email or phone
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'validation_errors', 'errors' => $validator->errors()], 422);
        }

        $type = $request->input('type', 'email'); // Default type to 'email' if null
        $credentials = $request->only('email', 'password');

        $user = null;

        if ($type === 'email') {
            if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
                return response()->json(['message' => 'invalid_email_format', 'error' => true], 422);
            }
            $user = User::where('email', $credentials['email'])->first();
        } elseif ($type === 'phone') {
            $formattedPhone = $this->formatPhoneForStorage($credentials['email']);
            $user = User::where('phone', $formattedPhone)->first();
        }

        // Check if user exists
        if (!$user) {
            return response()->json(['message' => "{$type}_doesnt_exist", 'error' => false], 401);
        }

        // Check if the user's password matches
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'wrong_password', 'error' => false], 401);
        }

        // Check if the user needs to add personal data
        if (!$user->info_filled && $type === 'email') {
            return response()->json(['message' => 'add_personal_data', "unique_id" => $user->unique_id]);
        }

        // Check if the email is verified
        if ($type === 'email' && !$user->email_verified) {
            return response()->json(['message' => 'email_not_verified', 'error' => false], 403);
        }

        // Generate token
        $token = $user->createToken('AuthToken')->plainTextToken;
        $createdAt = Carbon::now();
        $expiresAt = $createdAt->copy()->addDays(7);

        return response()->json([
            'token' => $token,
            'info_filled' => $user->info_filled,
            'created_at' => $createdAt->toDateTimeString(),
            'expires_at' => $expiresAt->toDateTimeString(),
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/refresh-token",
     *     summary="Refresh user's token",
     *     tags={"Auth"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", example="Bearer eyJhbGciOiJIUzI1...")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Token refreshed successfully"),
     *     @OA\Response(response="401", description="Invalid or expired token"),
     *     @OA\Response(response="404", description="User not found")
     * )
     */
    public function refreshToken(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'token_not_found'], 404);
        }


        $newToken = $user->createToken('AuthToken')->plainTextToken;
        $createdAt = Carbon::now();
        $expiresAt = $createdAt->copy()->addDays(3);

        return response()->json([
            'new_token' => $newToken,
            'created_at' => $createdAt->toDateTimeString(),
            'expires_at' => $expiresAt->toDateTimeString()
        ], 200);
    }



    /**
     * @OA\Post(
     *     path="/api/personal-data",
     *     summary="Add personal data",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "last_name", "phone"},
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="unique_id", type="string", example="123456"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Profile updated successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function personalData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required|string',
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|unique:users|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $token = $request->bearerToken();
        $user = User::where('unique_id', $request->unique_id)->first();

        if (!$user) {
            return response()->json(['error' => 'invalid_unique_id'], 401);
        }

        $user->info_filled = true;
        $user->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'is_register' => true,
        ]);
        $token = $user->createToken('AuthToken')->plainTextToken;

        return response()->json(['token' => $token, 'message' => 'data_added_successfully'], 200);
    }




    /**
     * @OA\Post(
     *     path="/api/check-email",
     *     summary="Check if email exists",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@mail.ru")
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Email exists"),
     *     @OA\Response(response="404", description="Email does not exist")
     * )
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $emailExists = User::where('email', $request->email)->exists();
        if ($emailExists) {
            return response()->json(['message' => 'true']);
        } else {
            return response()->json(['message' => 'false']);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/google-login",
     *     summary="Login or register a user using Google access token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", example="ya29.a0AfH6SM...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1)
     *             ),
     *             @OA\Property(property="token", type="string", example="1|eXaMpLeToKeN12345")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function googleLoginViaAccessToken(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string']
        ]);

        $token = $request->input('token');
        Log::info('Google Access Token: ' . $token);

        try {
            $googleUser = Socialite::driver('google')->userFromToken($token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token or unable to fetch Google user data'], 401);
        }

        if (empty($googleUser)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user = User::where('email', $googleUser->getEmail())->first();
        $password = Str::random(16);

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $user->email_verified = true;
            $user->email_verified_at = now();
            $user->save();

            Mail::to($user->email)->send(new GoogleLoginSuccess($user, $password));
        }

        if (!$user->info_filled) {
            return response()->json(['unique_id' => $user->unique_id, 'message' => 'add_personal_data']);
        }

        $token = $user->createToken('LOGIN');

        return response()->json([
            'user' => [
                'id' => $user->id,
            ],
            'info_filled' => $user->info_filled,
            'unique_id' => $user->unique_id,
            'token' => $token->plainTextToken,
            "message" => "sent_your_info_to_your_email"
        ]);
    }

    /**
     * Send OTP to a phone number.
     *
     * @OA\Post(
     *     path="/api/send-otp",
     *     summary="Send OTP",
     *     description="Send OTP to a specific phone number.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone_number"},
     *             @OA\Property(property="phone_number", type="string", example="998900000000")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="OTP sent successfully."))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send OTP",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Failed to send OTP."))
     *     )
     * )
     */
    public function sendOTP(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|regex:/^[0-9]{9,15}$/',
        ]);

        $phoneNumber = $request->input('phone_number');

        // Telefon raqamini saqlash uchun formatga aylantirish
        $formattedPhone = $this->formatPhoneForStorage($phoneNumber);

        $user = User::where('phone', $formattedPhone)->where('is_register', false);
        if ($user->exists()) {
            return response()->json(['message' => 'phone_number_exists'], 400);
        }

        // Telefon raqamini SMS yuborish uchun formatga aylantirish
        $phoneForSms = $this->formatPhoneForSms($phoneNumber);
        $otp = $this->otpService($phoneForSms, $this->generateOTP(), $formattedPhone);

        return $otp;
    }

    public function otpService($phoneNumber, $otp, $formattedPhone)
    {
        $username = config('sms.username');
        $password = config('sms.password');

        $response = Http::withBasicAuth($username, $password)->post(config('sms.url'), [
            "messages" => [
                [
                    "recipient" => $phoneNumber,
                    "message-id" => uniqid("msg_"),
                    "sms" => [
                        "originator" => "3700",
                        "content" => [
                            "text" => "Optomall sizning ro'yhatdan o'tish uchun kodingiz : $otp",
                        ],
                    ],
                ],
            ],
        ]);

        $tempUser = TempUser::create([
            'phone_number' => $formattedPhone, // Saqlash uchun formatlangan
            'otp' => $otp,
        ]);

        if ($response->failed()) {
            return response()->json(['status' => 'error', 'message' => 'send_code_failed'], 500);
        }

        return response()->json(['id' => $tempUser->id, 'message' => 'coniform_code_sended'], 200);
    }

    // Telefon raqamini saqlash uchun formatlash
    private function formatPhoneForStorage($phoneNumber)
    {
        return preg_replace('/^(\d{3})(\d{2})(\d{3})(\d{2})(\d{2})$/', '998 $2 $3 $4 $5', $phoneNumber);
    }

    // Telefon raqamini SMS yuborish uchun formatlash
    private function formatPhoneForSms($phoneNumber)
    {
        return preg_replace('/\s+/', '', $phoneNumber); // Bo'sh joylarni olib tashlash
    }

    // Start the session

    public function generateOTP($length = 6)
    {
        do {
            $otp = str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        } while (isset($_SESSION['generated_otps']) && in_array($otp, $_SESSION['generated_otps']));

        $_SESSION['generated_otps'][] = $otp;

        $_SESSION['generated_otps'] = array_filter($_SESSION['generated_otps'], function ($otp) {
            return isset($otp['timestamp']) && (time() - $otp['timestamp']) < 300;
        });

        return $otp;
    }


    /**
     * Verify an OTP and set the user's password.
     *
     * @OA\Post(
     *     path="/api/verify-otp",
     *     summary="Verify OTP and set password",
     *     description="Verify the provided OTP and set the user's password if OTP is correct.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"otp", "password", "confirm_password"},
     *             @OA\Property(property="otp", type="string", example="123456"),
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="confirm_password", type="string", example="newpassword123"),
     *             @OA\Property(property="name", type="string", example="AAA")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified and password updated successfully",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="OTP verified and password updated successfully."))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP or password mismatch",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Invalid OTP or password mismatch."))
     *     )
     * )
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
            'id' => "required|integer",
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',
            'name' => 'nullable|string',
        ]);

        // Foydalanuvchini vaqtinchalik jadvaldan topish
        $tempUser = TempUser::where('id', $request->input('id'))->first();

        if (!$tempUser) {
            return response()->json(['status' => 'error', 'message' => 'user_not_found'], 400);
        }

        if ($request->input('otp') != $tempUser->otp) {
            return response()->json(['status' => 'error', 'message' => 'incorect_coniform_code'], 400);
        }

        // Foydalanuvchini yaratish yoki yangilash
        $user = User::create(
            [
                'phone' => $tempUser->phone_number,
                'password' => Hash::make($request->input('password')),
                'is_register' => false,
                'name' => $request->input('name'),
                'info_filled' => true
            ],
        );

        $token = $user->createToken('AuthToken')->plainTextToken;

        // Vaqtinchalik foydalanuvchini o'chirish
        $tempUser->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'user_registered_successfully',
            'token' => $token,
            'user' => $user
        ], 200);
    }
}
