<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class ResetPasswordController extends Controller
{
    /**
     * Reset user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset user password",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "token"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="new_password"),
     *             @OA\Property(property="token", type="string", example="reset_token_received_in_email")
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Password reset successfully"),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="404", description="Invalid reset token or email"),
     *     @OA\Response(response="500", description="Failed to reset password")
     * )
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $token = $request->input('token');

        // Verify the token and email combination
        $reset = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Invalid reset token or email'], 404);
        }

        // Update user password
        try {
            DB::table('users')
                ->where('email', $email)
                ->update(['password' => Hash::make($password)]);

            // Remove reset token from database
            DB::table('password_resets')
                ->where('email', $email)
                ->delete();

            return response()->json(['message' => 'Password reset successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to reset password'], 500);
        }
    }
}
