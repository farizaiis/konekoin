<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Resources\Transaction as ResourcesTransaction;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController as BaseController;
use App\Jobs\TopUpProccessKonekita;
use App\Models\HistoryBalance;
use App\Models\User;

class TransactionController extends BaseController
{

   /**
    * @OA\Get(
    *     path="/api/v1/transactions",
    *     operationId="transactionList",
    *     tags={"Transaction"},
    *     summary="Transaction List",
    *     description="Transaction List here",
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
    *         description="Transactions retrieved successfully",
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

        $Transaction = Transaction::orderByDesc('created_at')->paginate($size);
        
        
        return $this->sendResponse($Transaction, 'Transaction retrieved successfully.');
    }

    /**
    * @OA\Post(
    *     path="/api/v1/transactions",
    *     operationId="transactionCreate",
    *     tags={"Transaction"},
    *     summary="Transaction Create",
    *     description="Transaction Create here",
    *     security={{"api_key":{}}},
    *     @OA\RequestBody(
    *         @OA\JsonContent(),
    *         @OA\MediaType(
    *            mediaType="multipart/form-data",
    *            @OA\Schema(
    *               type="object",
    *               required={"user_id", "app", "type", "durianpay_id"},
    *               @OA\Property(property="user_id", type="integer"),
    *               @OA\Property(property="app", type="text"),
    *               @OA\Property(property="konekita_order_id", type="integer"),
    *               @OA\Property(property="konekios_order_id", type="integer"),
    *               @OA\Property(property="konekoin_balance_id", type="text"),
    *               @OA\Property(property="type", type="text"),
    *               @OA\Property(property="durianpay_id", type="text"),
    *               @OA\Property(property="access_token", type="text"),
    *               @OA\Property(property="customer_id", type="text"),
    *               @OA\Property(property="payment_id", type="text"),
    *               @OA\Property(property="signature", type="text")
    *            ),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Transaction created successfully.",
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
            'app' => ['required', 'integer'],
            'konekita_order_id' => ['integer'],
            'konekios_order_id' => ['integer'],
            'konekoin_balance_id' => ['string'],
            'type' => ['required', 'string'],
            'durianpay_id' => ['required', 'string'],
            'access_token' => ['required', 'string'],
            'customer_id' => ['required', 'string'],
            'payment_id' => ['string'],
            'signature' => ['string']
        ]);
    
        if($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
    
        $Transaction = Transaction::create($input);
    
        return $this->sendResponse(new ResourcesTransaction($Transaction), 'Transaction created successfully.', 201);
    }

    /**
    * @OA\Get(
    *     path="/api/v1/transactions/{durianpay_id}",
    *     operationId="transactionRetrieve",
    *     tags={"Transaction"},
    *     summary="Transaction Retrieve",
    *     description="Transaction Retrieve here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="durianpay_id",
    *         in="path",
    *         description="Transaction durianpay_id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Transaction retrieved successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=404, description="Transaction not found"),
    * )
    */
    public function show($durianpay_id)
    {
        $Transaction = Transaction::where('durianpay_id', $durianpay_id)->first();
  
        if (is_null($Transaction)) {
            return $this->sendError('Transaction not found.');
        }
   
        return $this->sendResponse(new ResourcesTransaction($Transaction), 'Transaction retrieved successfully.');
    }

   /**
    * @OA\Put(
    *     path="/api/v1/transactions/{durianpay_id}",
    *     operationId="transactionUpdate",
    *     tags={"Transaction"},
    *     summary="Transaction Update",
    *     description="Transaction Update here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="durianpay_id",
    *         in="path",
    *         description="Transaction durianpay_id",
    *         required=true,
    *     ),
    *     @OA\Parameter(
    *         name="user_id",
    *         in="query",
    *         description="User Id",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="app",
    *         in="query",
    *         description="Transaction app",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="konekita_order_id",
    *         in="query",
    *         description="Transaction konekita_order_id",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="konekios_order_id",
    *         in="query",
    *         description="Transaction konekios_order_id",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="konekoin_balance_id",
    *         in="query",
    *         description="Transaction konekoin_balance_id",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="type",
    *         in="query",
    *         description="Transaction type",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="durianpay_id",
    *         in="query",
    *         description="Transaction durianpay_id",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="access_token",
    *         in="query",
    *         description="Transaction access_token",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="customer_id",
    *         in="query",
    *         description="Transaction customer_id",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="signature",
    *         in="query",
    *         description="Transaction signature",
    *         required=false,
    *     ),
    *     @OA\Parameter(
    *         name="payment_id",
    *         in="query",
    *         description="Transaction payment_id",
    *         required=false,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Transaction updated successfully",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=400, description="Validation Error."),
    *     @OA\Response(response=409, description="Failed to update the Transaction."),
    *     @OA\Response(response=404, description="Transaction not found."),
    * )
    */
    public function update(Request $request, $durianpay_id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => ['integer'],
            'app' => ['integer'],
            'konekita_order_id' => ['string'],
            'konekios_order_id' => ['string'],
            'konekoin_balance_id' => ['string'],
            'type' => ['string'],
            'durianpay_id' => ['string'],
            'access_token' => ['string'],
            'customer_id' => ['string'],
            'payment_id' => ['string'],
            'signature' => ['string']
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
        
        $record = Transaction::where('durianpay_id', $durianpay_id)->first();

        if(is_null($record)) {
            return $this->sendError('Transaction not found.', 404);   
        }
        
        $success = $record->update($input);
       
        $success = $record->save();

        if(!$success) {
            return $this->sendError('Failed to update the Transaction.', 400);   
        }
   
        return $this->sendResponse(new ResourcesTransaction($record), 'Transaction updated successfully.');
    }

   /**
    * @OA\Delete(
    *     path="/api/v1/transactions/{durianpay_id}",
    *     operationId="transactionDelete",
    *     tags={"Transaction"},
    *     summary="Transaction Delete",
    *     description="Transaction Delete here",
    *     security={{"api_key":{}}},
    *     @OA\Parameter(
    *         name="durianpay_id",
    *         in="path",
    *         description="Transaction durianpay_id",
    *         required=true,
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Transaction deleted successfully.",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(response=400, description="Failed to delete the Transaction."),
    *     @OA\Response(response=404, description="Transaction not found."),
    * )
    */
    public function destroy($durianpay_id)
    {
        $record = Transaction::where('durianpay_id', $durianpay_id)->first();

        if(is_null($record)) {
            return $this->sendError('Transaction not found.', 404);   
        }

        $success = $record->delete();

        if(!$success) {
            return $this->sendError('Failed to delete the Transaction.', 400);  
        }
   
        return $this->sendResponse([], 'Transaction deleted successfully.');
    }


    public function durianpay_webhook(Request $request) {
        if($request->event == 'payment.completed') {
            $record = Transaction::where('durianpay_id', $request->data['konekita_order_id'])->first();

            if(is_null($record)) {
                return $this->sendError('Transaction not found.', 404);   
            }

            if($request->data['id']) {
                $record->update([
                    'payment_id' => $request->data['id'],
                    'signature' => $request->data['signature']
                ]);

                $record->save();

                if($record->app == 'Konekita') {
                    if($record->type == 'Top Up via Konekita') {
                        $user = User::find($record->user_id);

                        if(is_string($record->konekoin_balance_id)) {
                            $array = explode(',', $record->konekoin_balance_id);
                            
                            foreach($array as $value) {
                                if(is_numeric($value)) {
                                    $balance = HistoryBalance::find($value);
                                    
                                    if($balance->type_transaction == 'Top Up saldo Konekoin') {
                                        $user->update([
                                            'balance' => $user->balance + $balance->amount
                                        ]);
                                        $user->save();
                                    }

                                    $balance->update([
                                        'status' => 'Lunas'
                                    ]);
                                    $balance->save();
                                }
                            }
                        }

                        $set_third_party = new TopUpProccessKonekita($record, $user);

                        $this->dispatch($set_third_party);
                    }
                }
            } 
        }

        return $this->sendResponse(new ResourcesTransaction($record), 'Transaction updated successfully.');
    }
}
