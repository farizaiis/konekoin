<?php


use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::get('/register', [RegisteredUserController::class, 'create'])
//                 ->middleware('guest')
//                 ->name('register');

Route::post('/api/v1/register', [RegisteredUserController::class, 'store']);

// Route::get('/login', [AuthenticatedSessionController::class, 'create'])
//                 ->middleware('guest')
//                 ->name('login');

Route::post('/api/v1/login', [AuthenticatedSessionController::class, 'store']);

// Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
//                 ->middleware('guest')
//                 ->name('password.request');

Route::post('/api/v1/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');

Route::get('/api/v1/reset-password/{token}', [NewPasswordController::class, 'create'])
                ->middleware('guest')
                ->name('password.reset');

Route::post('/api/v1/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.update');

// Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
//                 ->middleware('auth')
//                 ->name('verification.notice');

Route::get('/api/v1/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'], function (EmailVerificationRequest $request) {
    // if (!$request->hasValidSignature()) {
    //     $response = [
    //         'success' => false,
    //         'message' => 'Please input a valid signature',
    //     ];

    //     return response()->json($response, 403);
    // }
})
                ->middleware(['throttle:6,1', 'signature'])
                ->name('verification.verify');

Route::post('/api/v1/email/verification-notification/{email}', [EmailVerificationNotificationController::class, 'store'])
                ->middleware(['throttle:6,1'])
                ->name('verification.send');

// Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
//                 ->middleware('auth')
//                 ->name('password.confirm');

Route::post('/api/v1/confirm-password', [ConfirmablePasswordController::class, 'store'])
                ->middleware('auth:api');

Route::post('/api/v1/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->middleware('auth:api')
                ->name('logout');

Route::post('/api/v1/change-password', [ChangePasswordController::class, 'store'])
                ->middleware('auth:api')
                ->name('change.password');
