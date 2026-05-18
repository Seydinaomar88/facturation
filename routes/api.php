<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuincaillerieController;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'env' => app()->environment()
    ]);
});

// AUTH
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// QUINCAILLERIES (public)
Route::get('/quincailleries', [QuincaillerieController::class, 'index']);
Route::get('/quincailleries/{id}', [QuincaillerieController::class, 'show']);
Route::post('/quincailleries', [QuincaillerieController::class, 'store']);
Route::put('/quincailleries/{id}', [QuincaillerieController::class, 'update']);
Route::delete('/quincailleries/{id}', [QuincaillerieController::class, 'destroy']);

// PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // CLIENTS
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::put('/clients/{id}', [ClientController::class, 'update']);
    Route::delete('/clients/{id}', [ClientController::class, 'destroy']);

    // FACTURES
    Route::get('/factures', [FactureController::class, 'index']);
    Route::post('/factures', [FactureController::class, 'store']);
    Route::get('/factures/{id}', [FactureController::class, 'show']);
    Route::put('/factures/{id}', [FactureController::class, 'update']);
    Route::delete('/factures/{id}', [FactureController::class, 'destroy']);
    
    Route::get('/factures/{id}/pdf', [FactureController::class, 'generatePdf']);
    Route::get('/factures/{id}/whatsapp', [FactureController::class, 'sendWhatsApp']);
});