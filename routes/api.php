<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('signup', [UserController::class, 'create']);
Route::post('login', [UserController::class, 'login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('user/{id}', [UserController::class, 'show']);
    Route::get('favorite', [UserController::class, 'getFavoriteList']);
    Route::post("favorite", [FavoriteController::class, "create"]);
    Route::delete("favorite", [FavoriteController::class, "destroy"]);
    Route::post("updateImage", [ImageController::class, "handleImage"]);
});
