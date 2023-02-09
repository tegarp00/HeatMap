<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HeatMapController;
use App\Http\Controllers\AuthController;

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


// this endpoint can only be accessed by the admin
Route::post('/create', [HeatMapController::class, 'store'])->middleware(["withAuth"]);

// get all to property data
Route::get('/allheatmap', [HeatMapController::class, 'index']);

// to find geoinformations with given keywords
Route::get('/search', [HeatMapController::class, 'search']);

// find location name by latitude and longitude also list popular area nearby within given radius
Route::get('/reverseArea', [HeatMapController::class, 'reverseAreaPopular']);

// find how many coordinates and average price per square meter in a circle in radius 1KM
Route::post('/area', [HeatMapController::class, 'averageInCircle']);


// login
Route::post("/login", [AuthController::class, "login"]);
// logout
Route::get("/logout", [AuthController::class, "logout"]);


// this endpoint is not use in our live application because its still an experimental features
Route::get('/reverse', [HeatMapController::class, 'reverseArea']);
Route::post('/area/detail', [HeatMapController::class, 'areaDetail']);
Route::get('/getCurrencies', [HeatMapController::class, 'getCurrencies']);
Route::get('/updateCurrencies', [HeatMapController::class, 'updateCurrencies']);

