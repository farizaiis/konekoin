<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\v1\BaseController as BaseController;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use App\Jobs\RetrieveInstagramFollowers;
use App\Jobs\RetrieveTiktokFollowers;
use App\Jobs\RetrieveTwtitterFollowers;
use App\Jobs\RetrieveYoutubeSubscriber;
use App\Models\Package;
use App\Models\PremiumContent;
use App\Models\UserPackageList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Storage;

class UserController extends BaseController
{
    /**
    * @OA\Get(
    *     path="/api/v1/users",
    *     operationId="UserList",
    *     tags={"User"},
    *     summary="User List",
    *     description="User List here",
    *     security={{"api_key":{}}}, 
    *     @OA\Parameter(
    *         name="size",
    *         in="query",
    *         description="Paginate size",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="page",
    *         in="query",
    *         description="Paginate page",
    *         required=false,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Users retrieved successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="Users Not Found"),
    * )
    */
    public function index(Request $request)
    {
        if ($request->input('size')) { 
            $size = $request->input('size');
        } else {
            // Set default pagination size
            $size = 10;
        }
        
        $Users = User::paginate($size);
        
        return $this->sendResponse($Users, 'Users retrieved successfully.');
    }

    /**
    * @OA\Get(
    *     path="/api/v1/users/{id}",
    *     operationId="UserRetrieveById",
    *     tags={"User"},
    *     summary="User Retrieve",
    *     description="User Retrieve here",
    *     security={{"api_key":{}}}, 
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="User id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="User retrieved successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="User Not Found"),
    * )
    */
    public function show(Request $request, $id)
    {
        $User = User::find($id);

        if(is_null($User)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse(new UserResource($User), 'User retrieved successfully.');
    }

     /**
    * @OA\Get(
    *     path="/api/v1/users/by_email/{email}",
    *     operationId="UserRetrieveByEmail",
    *     tags={"User"},
    *     summary="User Retrieve By Email",
    *     description="User Retrieve by email here",
    *     security={{"api_key":{}}}, 
    *     @OA\Parameter(
    *         name="email",
    *         in="path",
    *         description="User email",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="User retrieved successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="User Not Found"),
    * )
    */
    public function show_by_email(Request $request, $email)
    {
        $User = User::where('email', $email)->first();

        if(is_null($User)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse(new UserResource($User), 'User retrieved successfully.');
    }

    /**
    * @OA\Get(
    *     path="/api/v1/users/by_konekita_id/{konekita_id}",
    *     operationId="UserRetrieveByKonekitaId",
    *     tags={"User"},
    *     summary="User Retrieve By Konekita Id",
    *     description="User Retrieve by konekita id here",
    *     security={{"api_key":{}}}, 
    *     @OA\Parameter(
    *         name="konekita_id",
    *         in="path",
    *         description="User konekita_id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="User retrieved successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="User Not Found"),
    * )
    */
    public function show_by_konekita_id(Request $request, $konekita_id)
    {
        $User = User::where('user_konekita_id', $konekita_id)->first();

        if(is_null($User)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse(new UserResource($User), 'User retrieved successfully.');
    }

     /**
    * @OA\Get(
    *     path="/api/v1/users/by_konekios_id/{konekios_id}",
    *     operationId="UserRetrieveByKonekiosId",
    *     tags={"User"},
    *     summary="User Retrieve By Konekios Id",
    *     description="User Retrieve by konekios here",
    *     security={{"api_key":{}}}, 
    *     @OA\Parameter(
    *         name="konekios_id",
    *         in="path",
    *         description="User konekios id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="User retrieved successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="User Not Found"),
    * )
    */
    public function show_by_konekios_id(Request $request, $konekios_id)
    {
        $User = User::where('user_konekios_id', $konekios_id)->first();

        if(is_null($User)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse(new UserResource($User), 'User retrieved successfully.');
    }

    /**
    * @OA\Put(
    *     path="/api/v1/users/{id}",
    *     operationId="Update",
    *     tags={"User"},
    *     summary="User Update",
    *     description="User Update here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="User id",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="fullname",
    *         in="query",
    *         description="User full name",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="user_konekios_id",
    *         in="query",
    *         description="User konekios User ID",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="user_konekita_id",
    *         in="query",
    *         description="User konekita User ID",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="balance",
    *         in="query",
    *         description="User balance",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="is_premium",
    *         in="query",
    *         description="User premium status",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="phone_number",
    *         in="query",
    *         description="User phone number",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="ktp_number",
    *         in="query",
    *         description="User ktp number",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="ktp_file",
    *         in="query",
    *         description="User ktp file",
    *         required=false,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Updated the user profile successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=400, description="Bad request"),
    *     @OA\Response(response=404, description="Resource Not Found"),
    * )
    */
    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'fullname' => 'string',
            'user_konekios_id' => 'integer',
            'user_konekita_id' => 'integer',
            'balance' => 'string',
            'is_premium' => 'integer',
            'phone_number' => 'string',
            'ktp_number' => 'string',
            'ktp_file' => 'string'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
        
        $record = User::find($id);

        if(is_null($record)) {
            return $this->sendError('User not found.');   
        }
           
        $success = $record->update($input);

        if(!$success) {
            return $this->sendError('Failed to update the User.', 400);   
        }
   
        return $this->sendResponse(new UserResource($record), 'Updated the user profile successfully.');
    }

    /**
    * @OA\Delete(
    *     path="/api/v1/users/{id}",
    *     operationId="User",
    *     tags={"User"},
    *     summary="User Delete",
    *     description="User Delete here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="User id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Deleted the user successfully",
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
    public function destroy($id)
    {
        $user = User::find($id);

        if(is_null($user)) {
            return response()->json('User not found', 404);
        }

        $success = $user->delete();

        if(!$success) {
            return response()->json('Failed to delete the user', 400);
        }

        return response()->json('Deleted the user successfully');
    }
}
