<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/auth')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
