<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuincaillerieController;

// AUTH
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// QUINCAILLERIES (public pour pouvoir s'inscrire)
Route::post('/quincailleries', [QuincaillerieController::class, 'store']);
Route::get('/quincailleries', [QuincaillerieController::class, 'index']);

// PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // CLIENTS
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);

    // FACTURES
    Route::get('/factures', [FactureController::class, 'index']);
    Route::post('/factures', [FactureController::class, 'store']);

    Route::get('/factures/{id}/pdf', [FactureController::class, 'generatePdf']);
    Route::get('/factures/{id}/whatsapp', [FactureController::class, 'sendWhatsApp']);
});