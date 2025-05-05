<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::post('posts/{id}/restore', [PostController::class, 'restore']);
    Route::delete('posts/{id}/force', [PostController::class, 'forceDelete']);
});


