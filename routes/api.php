<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Mobile App API Routes
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/profile', [MemberController::class, 'profile']);
        Route::get('/dashboard', [MemberController::class, 'dashboard']);
        Route::get('/pembiayaan-detail', [MemberController::class, 'pembiayaanDetail']);
        Route::post('/checkout', [MemberController::class, 'checkout']);
        Route::post('/pay-installment', [MemberController::class, 'payInstallment']);

        // Pay Later (bayar sebelum gajian)
        Route::post('/pay-later', [MemberController::class, 'submitPayLater']);
        Route::get('/pay-later/history', [MemberController::class, 'payLaterHistory']);
    });
});
