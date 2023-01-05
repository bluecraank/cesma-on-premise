<?php

use App\Http\Controllers\VlanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\SSHController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\EndpointController;
use App\Http\Controllers\KeyController;
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
    Route::get('/', [DeviceController::class, 'index'])->name('dashboard');
    Route::get('/trunks', [DeviceController::class, 'trunks'])->name('trunks');
    Route::get('/bcp', [BackupController::class, 'sendMail']);
    Route::get('/vlans', [VlanController::class, 'index'])->name('vlans');
    Route::get('/vlans/{id}', [VlanController::class, 'getPortsByVlan'])->name('vlanports');
    Route::get('/locations', [LocationController::class, 'index'])->name('locations');
    Route::get('/perform-ssh', [SSHController::class, 'overview'])->name('perform-ssh');
    Route::get('/user-settings', [UserController::class, 'index'])->name('user-settings');
    Route::get('/system', [UserController::class, 'management'])->name('system');
    Route::get('/logs', [LogController::class, 'index'])->name('logs');
    Route::get('/switch/{id}/live', [DeviceController::class, 'live'])->name('live');
    Route::get('/backups', [BackupController::class, 'index'])->name('backups');
    Route::get('/switch/{id}/backups', [BackupController::class, 'getSwitchBackups'])->name('backups-switch');
    Route::get('/download/switch/backup/{id}', [BackupController::class, 'downloadBackup']);
    Route::get('/clients', [EndpointController::class, 'index'])->name('clients');

    // TEMP
    Route::get('/pubkey/show', [DeviceController::class, 'updateAllSwitches']);
    Route::get('/endpoints', [EndpointController::class, 'updateEndpoint']);

    // INIT
    Route::get('/encrypt-key', [SSHController::class, 'encrypt_key_index']);
    Route::post('/encrypt-key/save', [SSHController::class, 'encrypt_key_save']);
    
    // Perform SSH
    Route::post('/switch/perform-ssh', [SSHController::class, 'performSSH']);
    
    // Create routes
    Route::post('/switch/create', [DeviceController::class, 'store']);
    Route::post('/location/create', [LocationController::class, 'store']);
    Route::post('/building/create', [BuildingController::class, 'store']);
    Route::post('/vlan/create', [VlanController::class, 'store']);
    Route::post('/user/create', [UserController::class, 'store']);
    Route::post('/switch/sync-pubkeys', [DeviceController::class, 'syncPubkeys']);
    Route::post('/pubkey/add', [KeyController::class, 'store']);

    // Delete routes
    Route::delete('/switch/delete', [DeviceController::class, 'destroy']);
    Route::delete('/building/delete', [BuildingController::class, 'destroy']);
    Route::delete('/location/delete', [LocationController::class, 'destroy']);
    Route::delete('/vlan/delete', [VlanController::class, 'destroy']);
    Route::delete('/user/delete', [UserController::class, 'destroy']);
    Route::delete('/user/delete-pubkey', [UserController::class, 'deletePubkey']);
    Route::delete('/backup/delete', [BackupController::class, 'destroy']);
    Route::delete('/pubkey/delete', [KeyController::class, 'destroy']);

    // Update routes
    Route::put('/switch/update', [DeviceController::class, 'update']);
    Route::put('/switch/refresh', [DeviceController::class, 'refresh']);
    Route::put('/building/update', [BuildingController::class, 'update']);
    Route::put('/location/update', [LocationController::class, 'update']);
    Route::put('/vlan/update', [VlanController::class, 'update']);
    Route::put('/user/update', [UserController::class, 'update']);
    Route::put('/user/pubkey', [UserController::class, 'setPubkey']);
    
});

// Login
Auth::routes();