<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\v1\BaseController as BaseController;
use App\Http\Resources\HistoryBalance as HistoryBalanceResource;
use App\Models\HistoryBalance;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class HistoryBalanceController extends BaseController
{
    /**
    * @OA\Get(
    *     path="/api/v1/history_balances",
    *     operationId="HistoryBalanceList",
    *     tags={"History Balance"},
    *     summary="History Balance List",
    *     description="History Balance List here",
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
    *         description="History Balances retrieved successfully",
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
        
        $HistoryBalances = HistoryBalance::orderByDesc('created_at')->paginate($size);
    
        return $this->sendResponse($HistoryBalances, 'History Balances retrieved successfully.');
    }

    /**
    * @OA\Post(
    *     path="/api/v1/history_balances",
    *     operationId="HistoryBalanceCreate",
    *     tags={"History Balance"},
    *     summary="History Balance Create",
    *     description="History Balance Create here",
    *     security={{"api_key":{}}},
    *     @OA\RequestBody(
    *         @OA\JsonContent(),
    *         @OA\MediaType(
    *            mediaType="multipart/form-data",
    *            @OA\Schema(
    *               type="object",
    *               required={"user_id", "type_transaction", "title", "description", "amount", "app_name", "date"},
    *               @OA\Property(property="user_id", type="integer"),
    *               @OA\Property(property="type_transaction", type="text"),
    *               @OA\Property(property="title", type="text"),
    *               @OA\Property(property="description", type="text"),
    *               @OA\Property(property="amount", type="integer"),
    *               @OA\Property(property="app_name", type="text"),
    *               @OA\Property(property="date", type="text"),
    *               @OA\Property(property="status", type="text")
    *            ),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="History Balance created successfully.",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="Validation Error."),
    * )
    */
    public function store(Request $request)
    {
        $input = $request->all();

        $today = date('Y-m-d H:i');
    
        $validator = Validator::make($input, [
            'user_id' => ['required', 'integer'],
            'type_transaction' => ['required', 'string'],
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'amount' => ['required', 'integer'],
            'app_name' => ['required', 'string', 'in:Konekios,Konekita,konekios,konekita'],
            'status' => ['string', 'in:Menunggu,Berhasil,Ditolak,menunggu,berhasil,ditolak'],
            'date' => ['required', 'string', 'date_format:Y-m-d H:i', 'after_or_equal:'.$today]
        ]);
    
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }

        $user = User::find($request->user_id);

        if(is_null($user)) {
            return $this->sendError('User not found.');
        }
    
        $HistoryBalance = HistoryBalance::create($input);
    
        return $this->sendResponse(new HistoryBalanceResource($HistoryBalance), 'History Balance created successfully.', 201);
    } 
   
    /**
    * @OA\Get(
    *     path="/api/v1/history_balances/{id}",
    *     operationId="HistoryBalanceRetrieve",
    *     tags={"History Balance"},
    *     summary="History Balance Retrieve",
    *     description="History Balance Retrieve here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="History Balance id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="History Balance retrieved successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="History Balance not found"),
    * )
    */
    public function show($id)
    {
        $HistoryBalance = HistoryBalance::find($id);
  
        if (is_null($HistoryBalance)) {
            return $this->sendError('History Balance not found.');
        }
   
        return $this->sendResponse(new HistoryBalanceResource($HistoryBalance), 'History Balance retrieved successfully.');
    }
    
    /**
    * @OA\Put(
    *     path="/api/v1/history_balances/{id}",
    *     operationId="HistoryBalanceUpdate",
    *     tags={"History Balance"},
    *     summary="History Balance Update",
    *     description="History Balance Update here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="History Balance id",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="user_id",
    *         in="query",
    *         description="History Balance owner user id",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="type_transaction",
    *         in="query",
    *         description="History Balance type transaction",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="title",
    *         in="query",
    *         description="History Balance title",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="description",
    *         in="query",
    *         description="History Balance description",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="amount",
    *         in="query",
    *         description="History Balance amount",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="app_name",
    *         in="query",
    *         description="History Balance app name konekita/konekios",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="status",
    *         in="query",
    *         description="History Balance status Berhasil/Menunggu/Ditolak",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="date",
    *         in="query",
    *         description="History Balance date",
    *         required=false,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="History Balance updated successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=400, description="Validation Error."),
    *     @OA\Response(response=404, description="History Balance not found."),
    *     @OA\Response(response=409, description="Failed to update the History Balance."),
    * )
    */
    public function update($id, Request $request)
    {
        $input = $request->all();

        $today = date('Y-m-d H:i:s');

        $validator = Validator::make($input, [
            'user_id' => ['integer'],
            'type_transaction' => ['string'],
            'title' => ['string'],
            'description' => ['string'],
            'amount' => ['integer'],
            'app_name' => ['string', 'in:Konekios,Konekita,konekios,konekita'],
            'status' => ['string', 'in:Menunggu,Berhasil,Ditolak,menunggu,berhasil,ditolak'],
            'date' => ['string', 'after_or_equal:'.$today]
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
        
        $record = HistoryBalance::find($id);

        if(is_null($record)) {
            return $this->sendError('History Balance not found.');   
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
            return $this->sendError('Failed to update the History Balance.', 409);   
        }
   
        return $this->sendResponse(new HistoryBalanceResource($record), 'History Balance updated successfully.');
    }

    /**
    * @OA\Delete(
    *     path="/api/v1/history_balances/{id}",
    *     operationId="HistoryBalanceDelete",
    *     tags={"History Balance"},
    *     summary="History Balance Delete",
    *     description="History Balance Delete here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="History Balance id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="History Balance deleted successfully.",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=400, description="Failed to delete the History Balance."),
    *     @OA\Response(response=404, description="History Balance not found."),
    * )
    */
    public function destroy($id)
    {
        $record = HistoryBalance::find($id);

        if(is_null($record)) {
            return $this->sendError('History Balance not found.');   
        }

        $success = $record->delete();

        if(!$success) {
            return $this->sendError('Failed to delete the History Balance.', 400);  
        }
   
        return $this->sendResponse([], 'History Balance deleted successfully.');
    }
}
