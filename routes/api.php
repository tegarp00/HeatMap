<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HeatMapController;

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


Route::post('/create', [HeatMapController::class, 'store']);
Route::get('/allheatmap', [HeatMapController::class, 'index']);
Route::post('/area', [HeatMapController::class, 'averageInCircle']);
Route::post('/area/detail', [HeatMapController::class, 'areaDetail']);
Route::get('/search', [HeatMapController::class, 'search']);