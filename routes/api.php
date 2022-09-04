<?php

use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Auth\OAuthController as OAuthControllerAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

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

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('stats/total-streams-by-game', [StatsController::class, 'totalStreamsByGame']);
    Route::get('stats/top-views-by-game', [StatsController::class, 'topViewsByGame']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::get('oauth/url/{provider}', [OAuthControllerAlias::class, 'fetchUrl']);
    Route::get('oauth/{provider}/callback', [OAuthControllerAlias::class, 'handleCallback']);
});
