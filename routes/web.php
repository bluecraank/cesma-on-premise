<?php

use App\Http\Controllers\SwitchController;
use App\Http\Controllers\VlanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\SSHController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/', [DeviceController::class, 'overview'])->name('dashboard');
    Route::get('/trunks', [DeviceController::class, 'trunks'])->name('trunks');
    Route::get('/vlans', [VlanController::class, 'index'])->name('vlans');
    Route::get('/locations', [LocationController::class, 'index'])->name('locations');
    Route::get('/perform-ssh', [SSHController::class, 'overview'])->name('perform-ssh');
    Route::get('/user-settings', [SSHController::class, 'overview'])->name('user-settings');
    Route::get('/logs', [SSHController::class, 'overview'])->name('logs');
    Route::get('/switch/live/{id}', [DeviceController::class, 'live'])->name('live');

    // Create new device
    Route::post('/switch/create', [DeviceController::class, 'store']);
    Route::post('/location/create', [LocationController::class, 'store']);
    Route::post('/building/create', [BuildingController::class, 'store']);

    // Delete routes
    Route::delete('/switch/delete', [DeviceController::class, 'destroy']);
    Route::delete('/building/delete', [BuildingController::class, 'destroy']);
    Route::delete('/location/delete', [LocationController::class, 'destroy']);
    Route::delete('/vlan/delete', [VlanController::class, 'destroy']);

    // Update routes
    Route::put('/switch/update', [DeviceController::class, 'update']);
    Route::put('/switch/refresh', [DeviceController::class, 'refresh']);

    Route::put('/building/update', [BuildingController::class, 'update']);
    Route::put('/location/update', [LocationController::class, 'update']);
    Route::put('/vlan/update', [VlanController::class, 'update']);
    
});

// Login
Auth::routes();