<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
    * @OA\Get(
    *     path="/api/v1/verify-email/{id}/{hash}",
    *     operationId="authVerify",
    *     tags={"Authentication"},
    *     summary="Verify Email",
    *     security={{"api_key":{}}},
    *     description="Verify Email here",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="User id",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="hash",
    *         in="path",
    *         description="Verification hash",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="expires",
    *         in="query",
    *         description="Verification expires at",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="signature",
    *         in="query",
    *         description="Verification signature",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Verified email successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(
    *         response=422,
    *         description="Unprocessable Entity",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=400, description="Bad request"),
    * )
    */
    public function __invoke(Request $request)
    {
        $request->validate([
            'expires' => ['required', 'integer'],
            'signature' => ['required', 'string']
        ]);
        
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            // return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
            $success['user'] = $user;
            $message = 'Email has already been verified';
            $response = [
                'success' => true,
                'data'    => $success,
                'message' => $message,
            ];
            return response()->json($response, 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $success['user'] = $user;
        $message = 'Verified email successfully.';

        $response = [
            'success' => true,
            'data'    => $success,
            'message' => $message,
        ];

        return response()->json($response, 200);

        // return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
    }
}
