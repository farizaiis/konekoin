<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\User;
use stdClass;

class ChangePasswordController extends BaseController
{
    /**
     * @OA\Post(
     * path="/api/v1/change-password",
     * operationId="ChangePassword",
     * tags={"Authentication"},
     * summary="User Change Password",
     * description="User Change Password here",
     * security={{"api_key":{}}}, 
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"current_password", "new_password", "password_confirmation"},
     *                 @OA\Property(property="current_password", type="password"),
     *                 @OA\Property(property="new_password", type="password"),
     *                 @OA\Property(property="password_confirmation", type="password"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Success change password",
     *          @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Validation Error"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function store(Request $request)
    {
        $input = $request->all();
    
        $validator = Validator::make($input, [
            'current_password' => ['required', Rules\Password::defaults()],
            'new_password' => ['required_with:password_confirmation', 'same:password_confirmation', 'max:100', Rules\Password::defaults()],
            'password_confirmation' => ['required']
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }

        $user = Auth::user();

        $check_password = Hash::check($request->current_password, $user->password);

        if(!$check_password) {
            return $this->sendError('Invalid current password');
        }

        $user = User::find($user->id);

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        $user->save();

        $response = new stdClass();
        $response->message = 'Success change password';
        $response->new_token = $user->createToken('authToken')->accessToken;

        return response()->json($response, 200);
    }
}
