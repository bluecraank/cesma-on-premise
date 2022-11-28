<?php

use App\Http\Controllers\SwitchController;
use App\Http\Controllers\VlanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Start, Switchübersicht


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/', [SwitchController::class, 'overview']);
    Route::get('/trunks', [SwitchController::class, 'trunks']);
    Route::get('/vlans', [VlanController::class, 'overview']);
});

// Login
Auth::routes();