<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email" , example="admin@mail.com"),
     *             @OA\Property(property="password", type="string", format="password" , example="password123"),
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
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'incorrect_password'], 422);
        }

        $emailExists = User::where('email', $request->email)->exists();

        if ($emailExists) {
            return response()->json(['message' => 'email_exists'], 422);
        }

        $user->update([
            'email' => $request->email,
        ]);

        return response()->json(['message' => 'email_updated_successfully'], 200);
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

        return response()->json(['message' => 'name_updated_successfully'], 200);
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
    /**
     * @OA\Post(
     *     path="/api/profile/send-update-phone-otp",
     *     summary="Send OTP for updating user phone",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="998901234567"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="OTP sent successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function sendUpdatePhoneOTP(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $phoneNumber = $request->phone;
        $digits = preg_replace('/\D/', '', $phoneNumber);
        if (strlen($digits) === 9) {
            $digits = '998' . $digits;
        }

        // Format to storage format for uniqueness check
        if (strlen($digits) === 12) {
            $formattedStats = preg_replace('/^(\d{3})(\d{2})(\d{3})(\d{2})(\d{2})$/', '$1 $2 $3 $4 $5', $digits);
        } else {
            $formattedStats = $phoneNumber;
        }

        // Check uniqueness manually against stored format
        if (\App\Models\User::where('phone', $formattedStats)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['error' => ['phone' => ['The phone has already been taken.']]], 422);
        }

        $formattedPhoneForSms = $digits; // This will be 998901234567

        // Reuse OTP generation logic from AuthController or similar
        // For simplicity, generating here. Ideally move to a service.
        $otp = rand(100000, 999999);

        // Cache OTP for 5 minutes
        \Illuminate\Support\Facades\Cache::put('update_phone_otp_' . $user->id, [
            'otp' => $otp,
            'phone' => $formattedStats
        ], 300);

        try {
            // $text = "Kodini hech kimga bermang! Optomall platformasi da telefon raqamni o'zgartirish uchun tasdiqlash kodi: " . $otp;
            $text = "Kodini hech kimga bermang! Optomall platformasi ga ro'yxatdan o'tish uchun tasdiqlash kodi: " . $otp;
            $sms = app(\App\Services\SmsProviderService::class);
            $sms->sendSms($formattedPhoneForSms, $text);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'sms_sending_failed', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'otp_sent_successfully'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/profile/verify-update-phone-otp",
     *     summary="Verify OTP and update user phone",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"otp"},
     *             @OA\Property(property="otp", type="string", example="123456"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Phone updated successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="400", description="Invalid OTP")
     * )
     */
    public function verifyUpdatePhoneOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $cachedData = \Illuminate\Support\Facades\Cache::get('update_phone_otp_' . $user->id);

        if (!$cachedData || $cachedData['otp'] != $request->otp) {
            return response()->json(['message' => 'invalid_or_expired_otp'], 400);
        }

        $newPhone = $cachedData['phone'];

        $formattedPhone = $cachedData['phone'];

        $user->update([
            'phone' => $formattedPhone,
        ]);

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('update_phone_otp_' . $user->id);

        return response()->json(['message' => 'phone_updated_successfully'], 200);
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
     *             required={"old_password", "password", "confirm_password"},
     *             @OA\Property(property="old_password", type="string", example="old-password"),
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
            'old_password' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'confirm_password' => 'required|string|max:255|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'incorrect_old_password'], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'password_updated_successfully'], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/location-form",
     *     summary="Add or update location form",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="region", type="string", example="example.region"),
     *             @OA\Property(property="district", type="string", example="Olmazor"),
     *             @OA\Property(property="adress", type="string", example="Rakat maxalla"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Location added or updated successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function locationForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'region' => 'nullable|string',
            'district' => 'nullable|string',
            'adress' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'user_not_found'], 404);
        }

        $user->update([
            'region' => $request->region,
            'district' => $request->district,
            'adress' => $request->adress,

        ]);

        return response()->json(['message' => 'location_added_successfully'], 200);
    }

}
