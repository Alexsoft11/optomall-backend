<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ResetPasswordRequest;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
 *             required={"email", "otp", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="otp", type="string", example="123456"),
 *             @OA\Property(property="password", type="string", format="password", example="new-password")
 *         ),
 *     ),
 *     @OA\Response(response="200", description="Password reset successful"),
 *     @OA\Response(response="401", description="Unauthorized"),
 *     @OA\Response(response="422", description="Validation errors")
 * )
 */
    private $otp;
    public function __construct(){
        $this->otp=new Otp();
    }
    public function resetPassword(ResetPasswordRequest $request){
        $otp2=$this->otp->validate($request->email,$request->otp);
        if(! $otp2->status){
            return response()->json(['error'=>$otp2],401);

        }
        $user=User::where('email',$request->email)->first();
        $user->update(
            [
                'password'=>Hash::make($request->password)
            ]
        );
       $user->tokens()->delete();
        $success['succees']=true;
        return response()->json($success,200);
    }
}