<?php

use App\Http\Controllers\VlanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\SSHController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\KeyController;
use App\Http\Controllers\SystemController;
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

// Basic routes allowed for all users
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [DeviceController::class, 'index'])->name('dashboard');
    Route::get('/vlans', [VlanController::class, 'index'])->name('vlans');
    Route::get('/vlans/{id}', [VlanController::class, 'getPortsByVlan'])->name('vlanports')->where('id', '[0-9]+');
    Route::get('/locations', [LocationController::class, 'index'])->name('locations');
    Route::get('/user-settings', [SystemController::class, 'index_usersettings'])->name('user-settings');
    Route::get('/system', [SystemController::class, 'index_system'])->name('system');
    Route::get('/logs', [LogController::class, 'index'])->name('logs');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::put('/user-settings/update', [SystemController::class, 'updateTheme']);
});
Route::prefix('switch')->middleware('auth:sanctum')->group(function () {
    Route::get('/backups', [BackupController::class, 'index'])->name('backups');
    Route::get('/trunks', [DeviceController::class, 'view_trunks'])->name('trunks');
    Route::get('/uplinks', [DeviceController::class, 'view_uplinks'])->name('uplinks');
    Route::get('/{id}/backups', [BackupController::class, 'getBackupsBySwitchId'])->name('backups-switch')->where('id', '[0-9]+');
    Route::get('/{id}', [DeviceController::class, 'view_details'])->name('details')->where('id', '[0-9]+');
    Route::get('/backup/{id}/download/', [BackupController::class, 'downloadBackup']);
});


// Admin only routes
Route::middleware(['role.admin', 'auth:sanctum'])->group(function () {
    Route::get('/execute', [SSHController::class, 'index'])->name('perform-ssh');
    Route::post('/location/create', [LocationController::class, 'store']);
    Route::post('/building/create', [BuildingController::class, 'store']);
    Route::post('/vlan/create', [VlanController::class, 'store']);
    Route::post('/pubkey/add', [KeyController::class, 'store']);
    Route::delete('/building/delete', [BuildingController::class, 'destroy']);
    Route::delete('/location/delete', [LocationController::class, 'destroy']);
    Route::delete('/vlan/delete', [VlanController::class, 'destroy']);
    Route::delete('/pubkey/delete', [KeyController::class, 'destroy']);
    Route::put('/building/update', [BuildingController::class, 'update']);
    Route::put('/location/update', [LocationController::class, 'update']);
    Route::put('/vlan/update', [VlanController::class, 'update']);
});

Route::prefix('switch')->middleware(['role.admin', 'auth:sanctum'])->group(function () {
    Route::post('/{id}/backup/create', [DeviceController::class, 'createBackup'])->where('id', '[0-9]+');;
    Route::post('/{id}/ssh/execute', [SSHController::class, 'performSSH'])->where('id', '[0-9]+');
    Route::post('/{id}/ssh/pubkeys', [DeviceController::class, 'uploadPubkeysToSwitch'])->where('id', '[0-9]+');
    Route::post('/{id}/refresh', [DeviceController::class, 'refresh'])->where('id', '[0-9]+');
    Route::post('/{id}/port-vlans/untagged', [DeviceController::class, 'setUntaggedVlanToPort'])->where('id', '[0-9]+');
    Route::post('/{id}/port-vlans/tagged', [DeviceController::class, 'setTaggedVlanToPort'])->where('id', '[0-9]+');
    Route::post('/backup/restore', [DeviceController::class, 'restoreBackup']);
    Route::delete('/backup/delete', [BackupController::class, 'destroy']);
    Route::post('/create', [DeviceController::class, 'store']);
    Route::put('/update', [DeviceController::class, 'update']);
    Route::delete('/delete', [DeviceController::class, 'destroy']);
    Route::put('/uplinks/update', [DeviceController::class, 'updateUplinks']);
    Route::post('/every/backup/create', [DeviceController::class, 'createBackupAllDevices']);
    Route::post('/every/clients', [ClientController::class, 'getClientsAllDevices']);
    Route::post('/every/pubkeys', [DeviceController::class, 'uploadPubkeysAllDevices']);
    Route::post('/every/vlans', [DeviceController::class, 'updateVlansAllDevices'])->name('Sync VLAN Results');
});

// Login
Auth::routes();
