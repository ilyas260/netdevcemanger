<?php

use App\Http\Controllers\Api\ScanSyncController;
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

Route::post('/scan/sync', [ScanSyncController::class, 'sync']);
Route::get('/agencies', [ScanSyncController::class, 'agencies']);
