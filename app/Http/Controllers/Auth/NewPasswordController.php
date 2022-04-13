<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    public function create(Request $request)
    {   
        // page for reset password
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * @OA\Post(
     * path="/api/v1/reset-password",
     * operationId="authResetPost",
     * tags={"Authentication"},
     * summary="Reset Password",
     * description="Reset Password Here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"token", "email", "password", "password_confirmation"},
     *                 @OA\Property(property="token", type="text"),
     *                 @OA\Property(property="email", type="email"),
     *                 @OA\Property(property="password", type="password"),
     *                 @OA\Property(property="password_confirmation", type="password")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset Password Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Reset Password Failed"),
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'max:100', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            $message = 'Reset Password Successfully';
            $response = [
                'success' => true,
                'data'    => $status,
                'message' => $message,
            ];
            return response()->json($response, 200);
        } else {
            $message = 'Reset Password Failed';
            $response = [
                'success' => false,
                'data'    => $status,
                'message' => $message,
            ];
            return response()->json($response, 400);
        }

        // // If the password was successfully reset, we will redirect the user back to
        // // the application's home authenticated view. If there is an error we can
        // // redirect them back to where they came from with their error message.
        // return $status == Password::PASSWORD_RESET
        //             ? redirect()->route('login')->with('status', __($status))
        //             : back()->withInput($request->only('email'))
        //                     ->withErrors(['email' => __($status)]);
    }
}