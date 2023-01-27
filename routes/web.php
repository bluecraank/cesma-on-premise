<?php

use App\Http\Controllers\VlanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\SSHController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DevicePortStatController;
use App\Http\Controllers\DeviceUplinkController;
use App\Http\Controllers\EncryptionController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\RoomController;
use App\Services\DeviceService;
use App\Services\MacTypeService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
});
Route::prefix('switch')->middleware('auth:sanctum')->group(function () {
    Route::get('/backups', [BackupController::class, 'index'])->name('backups');
    Route::get('/uplinks', [DeviceUplinkController::class, 'index'])->name('uplinks');
    Route::get('/{device:id}', [DeviceController::class, 'show'])->name('details')->where('id', '[0-9]+');
    Route::get('/{device:id}/backups', [DeviceController::class, 'showBackups'])->name('backups-switch')->where('id', '[0-9]+');
    Route::get('/backup/{id}/download/', [BackupController::class, 'downloadBackup']);
    Route::get('/{device:id}/ports/{port}', [DevicePortStatController::class, 'index'])->name('port-details-specific')->where('id', '[0-9]+');
});

Route::prefix('debug')->middleware('auth:sanctum')->group(function () {
    Route::get('/switch/{device:id}/dashboard', [DeviceService::class, 'refreshDevice'])->name('details')->where('id', '[0-9]+');


});


// Admin only routes
Route::middleware(['role.admin', 'auth:sanctum'])->group(function () {
    Route::get('/execute', [SSHController::class, 'index'])->name('perform-ssh');

    Route::post('/location', [LocationController::class, 'store']);
    Route::post('/building', [BuildingController::class, 'store']);
    Route::post('/room', [RoomController::class, 'store']);
    Route::put('/building', [BuildingController::class, 'update']);
    Route::put('/location', [LocationController::class, 'update']);
    Route::put('/room', [RoomController::class, 'update']);
    Route::delete('/building', [BuildingController::class, 'destroy']);
    Route::delete('/location', [LocationController::class, 'destroy']);
    Route::delete('/room', [RoomController::class, 'destroy']);

    Route::post('/vlan', [VlanController::class, 'store']);
    Route::delete('/vlan', [VlanController::class, 'destroy']);    
    Route::put('/vlan', [VlanController::class, 'update']);

    
    Route::post('/pubkey/add', [PublicKeyController::class, 'store']);
    Route::delete('/pubkey/delete', [PublicKeyController::class, 'destroy']);

    Route::put('/user/role', [SystemController::class, 'updateUserRole']);

    Route::post('/clients/type', [MacTypeService::class, 'store']);
    Route::post('/clients/type/icon', [MacTypeService::class, 'storeIcon']);
    Route::delete('/clients/type', [MacTypeService::class, 'delete']);   

    Route::post('/privatekey/upload', function(Request $request) {
        $key = $request->input('key');
        return "<pre>".EncryptionController::encrypt($key)."</pre><br><b>Please create new file 'ssh.key' in storage/app/ and paste this encrypted key into it.</b>";
    });
    Route::get('/privatekey', function() {
        if(!Storage::disk('local')->get('ssh.key')) {
            return view('ssh.encrypt');
        } else {
            return response('SSH Key already exists', 400);
        }
    });
});

Route::prefix('switch')->middleware(['role.admin', 'auth:sanctum'])->group(function () {
    Route::post('/{id}/backup/create', [DeviceController::class, 'createBackup'])->where('id', '[0-9]+');
    Route::post('/{device:id}/vlans/sync', [DeviceController::class, 'syncVlans'])->where('id', '[0-9]+');
    Route::put('/uplinks', [DeviceService::class, 'storeCustomUplinks']);
    Route::post('/{id}/ssh/execute', [SSHController::class, 'performSSH'])->where('id', '[0-9]+');
    Route::post('/{device:id}/ssh/pubkeys', [DeviceController::class, 'uploadPubkeysToSwitch'])->where('id', '[0-9]+');
    Route::post('/{device:id}/refresh', [DeviceService::class, 'refreshDevice'])->where('id', '[0-9]+');
    Route::post('/{id}/port-vlans/untagged', [DeviceController::class, 'setUntaggedVlanToPort'])->where('id', '[0-9]+');
    Route::post('/{id}/port-vlans/tagged', [DeviceController::class, 'setTaggedVlanToPort'])->where('id', '[0-9]+');
    Route::post('/backup/restore', [DeviceController::class, 'restoreBackup']);
    Route::delete('/backup/delete', [BackupController::class, 'destroy']);
    Route::post('/create', [DeviceController::class, 'store']);
    Route::put('/update', [DeviceController::class, 'update']);
    Route::delete('/delete', [DeviceController::class, 'destroy']);
    Route::post('/every/backup/create', [DeviceController::class, 'createBackupAllDevices']);
    Route::post('/every/clients', [ClientController::class, 'getClientsAllDevices']);
    Route::post('/every/pubkeys', [DeviceController::class, 'uploadPubkeysAllDevices']);
    Route::post('/every/vlans', [DeviceController::class, 'updateVlansAllDevices'])->name('Sync VLAN Results');

    Route::get('/topology', [DeviceController::class, 'view_topology'])->name('topology');
});

// Login
Auth::routes();
