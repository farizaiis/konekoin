<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use GuzzleHttp\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use stdClass;
use Illuminate\Support\Facades\Validator;

class RegisteredUserController extends BaseController
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * @OA\Post(
     * path="/api/v1/register",
     * operationId="Register",
     * tags={"Register"},
     * summary="User Register",
     * description="User Register here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"fullname", "email", "password", "role"},
     *                 @OA\Property(property="email", type="text"),
     *                 @OA\Property(property="password", type="password"),
     *                 @OA\Property(property="fullname", type="text"),
     *                 @OA\Property(property="role", type="text"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Register Successfully",
     *          @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function store(Request $request)
    {
        $input = $request->all();
        
        $validator = Validator::make($input, [
            'fullname' => ['required', 'string', 'max:100'],
            'user_konekios_id' => ['integer', 'unique:users'],
            'user_konekita_id' => ['integer', 'unique:users'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:100', 'unique:users'],
            'password' => ['required_with:password_confirmation', 'max:100', Rules\Password::defaults()],
            'role' => ['required', 'in:Superadmin,superadmin,User,user']
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }

        $user = User::create([
            'fullname' => $request->fullname,
            'user_konekios_id' => $request->user_konekios_id,
            'user_konekita_id' => $request->user_konekita_id,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => ucfirst($request->role),
            'balance' => 0,
        ]);

        // event(new Registered($user));

        $user->save();

        Auth::login($user);

        $response = new stdClass();
        $response->message = 'Register Success.';
        $response->data = $user;
        $response->token = $user->createToken('authToken')->accessToken;

        return response()->json($response, 201);
    }
}
