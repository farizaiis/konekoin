<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * @OA\Post(
     * path="/api/v1/forgot-password",
     * operationId="authForgot",
     * tags={"Authentication"},
     * summary="Forgot Password",
     * description="Forgot Password Here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email"},
     *                 @OA\Property(property="email", type="email"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sent Reset Email Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=404, description="Sent Reset Email Failed"),
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email:rfc,dns'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // return $status == Password::RESET_LINK_SENT
        //             ? back()->with('status', __($status))
        //             : back()->withInput($request->only('email'))
        //                     ->withErrors(['email' => __($status)]);

        if ($status == Password::RESET_LINK_SENT) {
            $message = 'Sent Reset Email Successfully';
            $response = [
                'success' => true,
                'data'    => $status,
                'message' => $message,
            ];
            return response()->json($response, 200);
        } else {
            $message = 'Sent Reset Email Failed';
            $response = [
                'success' => false,
                'message' => $message,
            ];
            return response()->json($response, 400);
        }
    }
}