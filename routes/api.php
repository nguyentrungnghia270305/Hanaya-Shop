<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API routes cho quản lý đơn hàng
Route::prefix('orders')->group(function () {
    // Lấy tất cả đơn hàng
    Route::get('/', [OrderController::class, 'index']);
    
    // Lấy chi tiết một đơn hàng
    Route::get('/{id}', [OrderController::class, 'show']);
    
    // Cập nhật trạng thái đơn hàng
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
    
    // Hủy đơn hàng
    Route::patch('/{id}/cancel', [OrderController::class, 'cancel']);
    
    // Lấy đơn hàng theo trạng thái
    Route::get('/status/{status}', [OrderController::class, 'getByStatus']);
});
