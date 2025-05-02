<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocksController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('socks')->group(function () {
    Route::get('/', [SocksController::class, 'index']);
    Route::post('/income', [SocksController::class, 'income']);
    Route::post('/outcome', [SocksController::class, 'outcome']);
});


