<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;


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
            return response()->json(['message' => 'user_already_registrated'], 422);
        }
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'confirm_password' => Hash::make($request->confirm_password),
        ]);
        $user->notify(new VerifyEmailNotification($user));
        return response()->json(['message' => 'user_registered_successfully'], 201);
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
     *             @OA\Property(property="email", type="string", format="email", example="khamidovsherzod003@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678")
     *         ),
     *     ),
     *     @OA\Response(response="200", description="User logged in successfully"),
     *     @OA\Response(response="401", description="Incorrect login or password")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('AuthToken')->plainTextToken;

            return response()->json(['token' => $token], 200);
        }

        return response()->json(['message' => 'incorrect_login_or_password', 'error' => true], 401);
    }


     /**
     * @OA\Post(
     *     path="/api/personal-data",
     *     summary="Add personal data",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "last_name", "phone"},
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
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
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = Auth::user();
        $user->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
        ]);

        return response()->json(['message' => 'data_added_successfully'], 200);
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


}
