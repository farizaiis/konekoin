<?php

use App\Http\Controllers\Api\v1\BankAccountController;
use App\Http\Controllers\Api\v1\HistoryBalanceController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['auth:api']], function() {
    //User Entity
    Route::get('/v1/users', [UserController::class, 'index']);
    Route::get('/v1/users/{id}', [UserController::class, 'show']);
    Route::get('/v1/users/by_konekita_id/{konekita_id}', [UserController::class, 'show_by_konekita_id']);
    Route::get('/v1/users/by_konekios_id/{konekios_id}', [UserController::class, 'show_by_konekios_id']);
    Route::get('/v1/users/by_email/{email}', [UserController::class, 'show_by_email']);
    Route::put('/v1/users/{id}', [UserController::class, 'update']);

    //Bank Account Entity
    Route::post('/v1/bank_accounts', [BankAccountController::class, 'store']);
    Route::get('/v1/bank_accounts', [BankAccountController::class, 'index']);
    Route::get('/v1/bank_accounts/{id}', [BankAccountController::class, 'show']);
    Route::put('/v1/bank_accounts/{id}', [BankAccountController::class, 'update']);
    Route::delete('/v1/bank_accounts/{id}', [BankAccountController::class, 'destroy']);

    //History Balance Entity
    Route::post('/v1/history_balances', [HistoryBalanceController::class, 'store']);
    Route::get('/v1/history_balances', [HistoryBalanceController::class, 'index']);
    Route::get('/v1/history_balances/{id}', [HistoryBalanceController::class, 'show']);
    Route::put('/v1/history_balances/{id}', [HistoryBalanceController::class, 'update']);
    Route::delete('/v1/history_balances/{id}', [HistoryBalanceController::class, 'destroy']);

    //Transaction Entity
    Route::post('/v1/transactions', [TransactionController::class, 'store']);
    Route::get('/v1/transactions', [TransactionController::class, 'index']);
    Route::get('/v1/transactions/{durianpay_id}', [TransactionController::class, 'show']);
    Route::delete('/v1/transactions/{durianpay_id}', [TransactionController::class, 'destroy']);
    Route::put('/v1/transactions/{durianpay_id}', [TransactionController::class, 'update']);
});


//Webhook Durianpay
Route::post('/v1/transactions/webhook', [TransactionController::class, 'durianpay_webhook']);