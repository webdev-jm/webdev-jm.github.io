<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('logout', [AuthController::class, 'logout']);

    Route::get('user', function (Request $request) {
        return $request->user();
    });

    Route::post('sync/batch', [SyncController::class, 'batch'])->name('api.sync.batch');
    Route::get('sync/status', [SyncController::class, 'status'])->name('api.sync.status');

    Route::post('posts', [PostController::class, 'store'])->name('api.posts.store');
    Route::put('posts/{post}', [PostController::class, 'update'])->name('api.posts.update');
});
