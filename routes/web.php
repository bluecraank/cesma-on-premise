<?php

use App\ClientProviders\Baramundi;
use App\ClientProviders\SNMP_Routers;
use App\ClientProviders\SNMP_Sophos_XG;
use App\Devices\ArubaCX;
use App\Http\Controllers\VlanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\SSHController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\KeyController;
use App\Devices\ArubaOS;
use App\Http\Controllers\SnmpCollectorController;
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
    Route::get('/vlans', [VlanController::class, 'index'])->name('vlans');
    Route::get('/vlans/{id}', [VlanController::class, 'getPortsByVlan'])->name('vlanports');
    Route::get('/locations', [LocationController::class, 'index'])->name('locations');
    Route::get('/perform-ssh', [SSHController::class, 'overview'])->name('perform-ssh');
    Route::get('/user-settings', [UserController::class, 'index'])->name('user-settings');
    Route::get('/system', [UserController::class, 'index_system'])->name('system');
    Route::get('/logs', [LogController::class, 'index'])->name('logs');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::post('/location/create', [LocationController::class, 'store']);
    Route::post('/building/create', [BuildingController::class, 'store']);
    Route::post('/vlan/create', [VlanController::class, 'store']);
    Route::post('/user/create', [UserController::class, 'store']);
    Route::post('/pubkey/add', [KeyController::class, 'store']);
    Route::delete('/building/delete', [BuildingController::class, 'destroy']);
    Route::delete('/location/delete', [LocationController::class, 'destroy']);
    Route::delete('/vlan/delete', [VlanController::class, 'destroy']);
    Route::delete('/user/delete', [UserController::class, 'destroy']);
    Route::delete('/user/delete-pubkey', [UserController::class, 'deletePubkey']);
    Route::delete('/pubkey/delete', [KeyController::class, 'destroy']);
    Route::put('/building/update', [BuildingController::class, 'update']);
    Route::put('/location/update', [LocationController::class, 'update']);
    Route::put('/vlan/update', [VlanController::class, 'update']);
    Route::put('/user/update', [UserController::class, 'update']);
    Route::put('/user/pubkey', [UserController::class, 'addPubkey']);
});


Route::prefix('switch')->middleware('auth:sanctum', 'verified')->group(function() {
    // Allgemein
    Route::get('/backups', [BackupController::class, 'index'])->name('backups');
    Route::get('/trunks', [DeviceController::class, 'index_trunks'])->name('trunks');
    Route::get('/uplinks', [DeviceController::class, 'index_uplinks'])->name('uplinks');

    // Switch specific
    Route::get('/{id}/backups', [BackupController::class, 'getSwitchBackups'])->name('backups-switch');
    Route::get('/{id}/live', [DeviceController::class, 'index_live'])->name('live');
    Route::post('/{id}/backup/create', [DeviceController::class, 'createBackup'])->where('id', '[0-9]+');;
    Route::post('/{id}/ssh/execute', [SSHController::class, 'performSSH']);
    Route::post('/{id}/ssh/pubkeys', [DeviceController::class, 'uploadPubkeysToSwitch']);
    
    // Switch backups
    Route::get('/backup/{id}/download/', [BackupController::class, 'downloadBackup']);
    Route::post('/backup/restore', [DeviceController::class, 'restoreBackup']);
    Route::delete('/backup/delete', [BackupController::class, 'destroy']);

    // Standard CRUD operations
    Route::post('/create', [DeviceController::class, 'store']);
    Route::put('/update', [DeviceController::class, 'update']);
    Route::put('/{id}/refresh', [DeviceController::class, 'refresh']);
    Route::delete('/delete', [DeviceController::class, 'destroy']);
    Route::put('/uplinks/update', [DeviceController::class, 'updateUplinks']);
    Route::post('/{id}/ports/update', [DeviceController::class, 'updateUntaggedPorts']);


    // Posts for actions on all Switches
    Route::post('/every/backup/create', [DeviceController::class, 'createBackupAllDevices']);
    Route::post('/every/clients', [ClientController::class, 'getClientsAllDevices']);
    Route::post('/every/pubkeys', [DeviceController::class, 'uploadPubkeysAllDevices']);
    Route::post('/every/vlans', [DeviceController::class, 'updateVlansAllDevices'])->name('Sync VLAN Results');
});

Route::prefix('debug')->middleware('auth:sanctum', 'verified')->group(function() {

});

// Login
Auth::routes();