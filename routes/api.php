<?php

use App\Http\Controllers\ExpertController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\UserController;
use App\Models\Expert;
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
Route::get("image/{name}", [ImageController::class, "index"]);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('logout', [UserController::class, 'logout']);

    Route::get("home", [UserController::class, "index"]);

    Route::get('user/{id}', [UserController::class, 'show']);
    Route::patch('user', [UserController::class, 'update']);

    Route::get('expert/{id}', [ExpertController::class, 'index']);
    Route::post('expert', [ExpertController::class, 'create']);
    Route::patch('expert/{id}', [ExpertController::class, 'update']);
    Route::patch('expert/toggle/{id}', [ExpertController::class, 'toggleActive']);

    Route::post('specialty', [SpecialtyController::class, 'create']);
    Route::get('specialty/{id}', [SpecialtyController::class, 'search']);
    Route::get('specialties', [SpecialtyController::class, 'index']);
    
    Route::post("updateImage", [ImageController::class, "handleImage"]);

    Route::get('favorite', [UserController::class, 'getFavoriteList']);
    Route::post("favorite", [FavoriteController::class, "create"]);
    Route::delete("favorite", [FavoriteController::class, "destroy"]);

    Route::get('time/{id}', [TimeController::class, 'index']);
    Route::post('time', [TimeController::class, 'create']);
    Route::patch('time/{time}', [TimeController::class, 'update']);

    Route::get("reservation", [ReservationController::class, "index"]);
    Route::get("reservation/{id}", [ReservationController::class, "show"]);
    Route::post("reservation", [ReservationController::class, "create"]);

    Route::post('prepareConnection', [MessageController::class, 'prepareConnection']);
    Route::post("liveMessages", [MessageController::class, "liveMessages"]);
    Route::get("chat/{id}", [MessageController::class, 'getChat']);
});
