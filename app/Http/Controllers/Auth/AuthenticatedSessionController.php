<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use stdClass;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * @OA\Post(
     * path="/api/v1/login",
     * operationId="authLogin",
     * tags={"Authentication"},
     * summary="User Login",
     * description="Login User here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", type="email"),
     *                 @OA\Property(property="password", type="password")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function store(Request $request, LoginRequest $loginrequest)
    {
        $request->validate([
            'email' => ['required', 'string', 'email:rfc,dns'],
            'password' => ['required', 'string']
        ]);

        $loginrequest->ensureIsNotRateLimited();

        $check_email = User::where('email', $request->email)->first();

        if(is_null($check_email)) {
            throw ValidationException::withMessages([
                'failed' => 'Email not registered',
            ]);
        }

        $loginrequest->authenticate();

        $loginrequest->session()->regenerate();

        // return redirect()->intended(RouteServiceProvider::HOME);

        $user= auth()->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->accessToken;


        $response = new stdClass();
        $response->message = 'Login Successfully';
        $response->data = auth()->user();
        $response->token = $token;

        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     * path="/api/v1/logout",
     * operationId="authLogout",
     * tags={"Authentication"},
     * summary="User Logout",
     * security={{"api_key":{}}}, 
     * description="Logout User here",
     *     @OA\Response(
     *         response=200,
     *         description="Logout Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function destroy(Request $request)
    {
        // Auth::guard('web')->logout();
        $user = auth()->user()->token();
        $request->session()->invalidate();
        $user->revoke();

        // $request->session()->regenerateToken();

        // return redirect('/');

        $response = new stdClass();
        $response->message = 'Logout Successfully';

        return response()->json($response, 200);
    }
}