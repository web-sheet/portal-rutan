<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
 

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/login', function () {
    return response()->json(['message' => 'login page']);
})->name('login');