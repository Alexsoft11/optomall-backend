<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ForgotPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
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
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com")
 *         ),
 *     ),
 *     @OA\Response(response="200", description="Password reset initiated"),
 *     @OA\Response(response="422", description="Validation errors")
 * )
 */
    public function forgotPassword(ForgotPasswordRequest $request){
        $input=$request->only('email');
        $user=User::where('email',$input)->first();
        $user->notify(new ResetPasswordNotification());
        $success['succees']=true;
        return response()->json($success,200);
    }
}