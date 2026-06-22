<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockHistoryController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);


    Route::post('/items', [ItemController::class, 'store']);


    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);

    Route::post('/requests/{id}/approve-kaur', [ItemRequestController::class, 'approveKaur']);
    Route::post('/requests/{id}/approve-kasi', [ItemRequestController::class, 'approveKasi']);
    Route::post('/requests/{id}/reject', [ItemRequestController::class, 'reject']);

    Route::delete('/requests/{id}', [ItemRequestController::class, 'destroy']);

    Route::get('/stock-history', [StockHistoryController::class, 'index']);


    // Pastikan endpoint ditaruh di atas route resource jika menggunakan apiResource
    Route::post('/pegawais/import', [PegawaiController::class, 'importExcel']);
    Route::post('/pegawais/bulk-delete', [PegawaiController::class, 'bulkDelete']);


    Route::apiResource('pegawais', PegawaiController::class);




    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Rute Manajemen User (Khusus Admin)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);

    Route::post('/items/bulk-delete', [ItemController::class, 'bulkDelete']);

    Route::post('/items/import-excel', [ItemController::class, 'importExcel']);



    Route::get('/settings/template', [TemplateController::class, 'getTemplate']);
    Route::post('/settings/template', [TemplateController::class, 'saveTemplate']);

    Route::get('/absensi/dashboard-stats', [AbsensiController::class, 'getDashboardStats']);
});



Route::apiResource('absensi', AbsensiController::class);

// PUBLIC
Route::post('/requests', [ItemRequestController::class, 'store']);

Route::get('/items', [ItemController::class, 'index']);

Route::get('/requests/{name}', [ItemRequestController::class, 'byEmployee']);

// ADMIN (sementara masih auth kalau kamu mau)
Route::get('/admin/requests', [ItemRequestController::class, 'index']);

Route::get('/requests', [ItemRequestController::class, 'index']);

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::get('/pegawai', [PegawaiController::class, 'index']);
