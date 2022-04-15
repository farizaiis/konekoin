<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\v1\BaseController as BaseController;
use App\Http\Resources\BankAccount as BankAccountResource;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends BaseController
{
    /**
    * @OA\Get(
    *     path="/api/v1/bank_accounts",
    *     operationId="bankAccountList",
    *     tags={"Bank Account"},
    *     summary="Bank Account List",
    *     description="Bank Account List here",
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
    *         description="Bank Accounts retrieved successfully",
    *         @OA\JsonContent()
    *     ),
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
        
        $BankAccounts = BankAccount::orderByDesc('created_at')->paginate($size);
    
        return $this->sendResponse($BankAccounts, 'Bank Accounts retrieved successfully.');
    }

    /**
    * @OA\Post(
    *     path="/api/v1/bank_accounts",
    *     operationId="bankAccountCreate",
    *     tags={"Bank Account"},
    *     summary="Bank Account Create",
    *     description="Bank Account Create here",
    *     security={{"api_key":{}}},
    *     @OA\RequestBody(
    *         @OA\JsonContent(),
    *         @OA\MediaType(
    *            mediaType="multipart/form-data",
    *            @OA\Schema(
    *               type="object",
    *               required={"user_id", "owner_name", "bank_name", "account_number"},
    *               @OA\Property(property="user_id", type="integer"),
    *               @OA\Property(property="owner_name", type="text"),
    *               @OA\Property(property="bank_name", type="text"),
    *               @OA\Property(property="account_number", type="integer")
    *            ),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Bank Account created successfully.",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="Validation Error."),
    * )
    */
    public function store(Request $request)
    {
        $input = $request->all();
    
        $validator = Validator::make($input, [
            'user_id' => ['required', 'integer'],
            'owner_name' => ['required', 'string'],
            'bank_name' => ['required', 'string', 'in:BCA,Mandiri'],
            'account_number' => ['required', 'integer']
        ]);
    
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
        
        $account_numb_to_string = (string)$request->account_number;
        $account_numb_digit = strlen($account_numb_to_string);

        if($request->bank_name == 'BCA' && $account_numb_digit != 10) {
            return $this->sendError('Please input a valid account number.');
        } elseif($request->bank_name == 'Mandiri' && $account_numb_digit != 13) {
            return $this->sendError('Please input a valid account number.');
        }

        $user = User::find($request->user_id);

        if(is_null($user)) {
            return $this->sendError('User not found.');
        }
    
        $BankAccount = BankAccount::create($input);
    
        return $this->sendResponse(new BankAccountResource($BankAccount), 'Bank Account created successfully.', 201);
    } 
   
    /**
    * @OA\Get(
    *     path="/api/v1/bank_accounts/{id}",
    *     operationId="bankAccountRetrieve",
    *     tags={"Bank Account"},
    *     summary="Bank Account Retrieve",
    *     description="Bank Account Retrieve here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="Bank Account id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Bank Account retrieved successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="Bank Account not found"),
    * )
    */
    public function show($id)
    {
        $BankAccount = BankAccount::find($id);
  
        if (is_null($BankAccount)) {
            return $this->sendError('Bank Account not found.');
        }
   
        return $this->sendResponse(new BankAccountResource($BankAccount), 'Bank Account retrieved successfully.');
    }
    
    /**
    * @OA\Put(
    *     path="/api/v1/bank_accounts/{id}",
    *     operationId="bankAccountUpdate",
    *     tags={"Bank Account"},
    *     summary="Bank Account Update",
    *     description="Bank Account Update here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="Bank Account id",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="user_id",
    *         in="query",
    *         description="Bank Account owner user id",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="owner_name",
    *         in="query",
    *         description="Bank owner name",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="bank_name",
    *         in="query",
    *         description="Bank name",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="account_number",
    *         in="query",
    *         description="Bank Account number",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Bank Account updated successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=400, description="Validation Error."),
    *     @OA\Response(response=404, description="Bank Account not found."),
    *     @OA\Response(response=409, description="Failed to update the Bank Account."),
    * )
    */
    public function update($id, Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => 'integer',
            'owner_name' => 'string',
            'bank_name' => 'string',
            'account_number' => 'integer'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
        
        $record = BankAccount::find($id);

        if(is_null($record)) {
            return $this->sendError('Bank Account not found.');   
        }

        if($request->user_id) {
            $user = User::find($request->user_id);

            if(is_null($user)) {
                return $this->sendError('User not found.');
            }
        }
   
        $success = $record->update($input);

        $success = $record->save();

        if(!$success) {
            return $this->sendError('Failed to update the Bank Account.', 409);   
        }
   
        return $this->sendResponse(new BankAccountResource($record), 'Bank Account updated successfully.');
    }

    /**
    * @OA\Delete(
    *     path="/api/v1/bank_accounts/{id}",
    *     operationId="bankAccountDelete",
    *     tags={"Bank Account"},
    *     summary="Bank Account Delete",
    *     description="Bank Account Delete here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="Bank Account id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Bank Account deleted successfully.",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=400, description="Failed to delete the Bank Account."),
    *     @OA\Response(response=404, description="Bank Account not found."),
    * )
    */
    public function destroy($id)
    {
        $record = BankAccount::find($id);

        if(is_null($record)) {
            return $this->sendError('Bank Account not found.');   
        }

        $success = $record->delete();

        if(!$success) {
            return $this->sendError('Failed to delete the Bank Account.', 400);  
        }
   
        return $this->sendResponse([], 'Bank Account deleted successfully.');
    }
}
