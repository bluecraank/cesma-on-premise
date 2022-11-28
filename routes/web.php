<?php

use App\Http\Controllers\SwitchController;
use App\Http\Controllers\VlanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SSHController;
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
    Route::get('/', [SwitchController::class, 'overview'])->name('dashboard');
    Route::get('/trunks', [SwitchController::class, 'trunks'])->name('trunks');
    Route::get('/vlans', [VlanController::class, 'overview'])->name('vlans');
    Route::get('/locations', [LocationController::class, 'overview'])->name('locations');
    Route::get('/perform-ssh', [SSHController::class, 'overview'])->name('perform-ssh');
    Route::get('/user-settings', [SSHController::class, 'overview'])->name('user-settings');
    Route::get('/logs', [SSHController::class, 'overview'])->name('logs');
});

// Login
Auth::routes();