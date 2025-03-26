<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
    Route::post('/admin/events', [EventController::class, 'store']);
    Route::get('/admin/show',[EventController::class, 'show_event']);
    Route::put('/admin/edit/{id}',[EventController::class, 'update']);
    Route::delete('/admin/delete/{id}', [EventController::class, 'destroy']);

});

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [UserController::class, 'index']);
    Route::get('/user/events', [UserController::class, 'user_event']);
    Route::post('/user/reserved',[UserController::class, 'user_reserved']);
});

