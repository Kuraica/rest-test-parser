<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ApiDataController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::get('fetch-data', [ApiController::class, 'fetchData']); // for testing

Route::get('/files-and-directories', [ApiDataController::class, 'getFilesAndDirectories']);
Route::get('/directories', [ApiDataController::class, 'getDirectories']);
Route::get('/files', [ApiDataController::class, 'getFiles']);
