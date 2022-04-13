<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
     /**
     * @OA\Post(
     * path="/api/v1/email/verification-notification/{email}",
     * operationId="authVerifySend",
     * tags={"Authentication"},
     * summary="Send Verification Link",
     * description="Send Verification Link here",
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="User email",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification Link Sent",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Please input a valid email, data not found"),
     * )
     */
    public function store(Request $request, $email)
    {   
        $request->merge(['email' => $email]);
        $request->validate([
            'email' => ['required', 'email:rfc,dns']
        ]);

        $user = User::where('email', $email)->first();

        if(is_null($user)) {
            $response = [
                'success' => false,
                'message' => 'Please input a valid email, data not found',
            ];

            return response()->json($response, 404);
        }

        if ($user->hasVerifiedEmail()) {
            // return redirect()->intended(RouteServiceProvider::HOME);
            // $success['user'] = auth()->user();
            $message = 'Email has already been verified';
            $response = [
                'success' => true,
                'data'    => $user,
                'message' => $message,
            ];
            return response()->json($response, 200);
        }

        $user->sendEmailVerificationNotification();

        $message = 'Verification Link Sent';
        $response = [
            'success' => true,
            'message' => $message,
        ];
        return response()->json($response, 200);

        // return back()->with('status', 'verification-link-sent');
    }
}