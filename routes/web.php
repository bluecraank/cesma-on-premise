<?php

use App\Devices\ArubaCX;
use App\Devices\DellEMC;
use App\Devices\DellEMCPowerSwitch;
use App\Http\Controllers\VlanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\SSHController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DevicePortStatController;
use App\Http\Controllers\DeviceUplinkController;
use App\Http\Controllers\PublicKeyController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SiteController;
use App\Http\Livewire\ShowBackups;
use App\Http\Livewire\ShowBuildings;
use App\Http\Livewire\ShowClients;
use App\Http\Livewire\ShowDevices;
use App\Http\Livewire\ShowLogs;
use App\Http\Livewire\ShowRooms;
use App\Http\Livewire\ShowSites;
use App\Http\Livewire\ShowSwitchBackups;
use App\Http\Livewire\ShowVlans;
use App\Models\Device;
use App\Services\DeviceService;
use App\Services\MacTypeService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
    Route::get('/', ShowDevices::class)->name('dashboard');
    Route::get('/vlans', ShowVlans::class)->name('vlans');
    Route::get('/vlans/{id}', [VlanController::class, 'getPortsByVlan'])->name('vlanports')->where('id', '[0-9]+');
    Route::get('/clients', ShowClients::class)->name('clients');
    
    Route::get('/system', [SystemController::class, 'index_system'])->name('system');
    Route::get('/logs', ShowLogs::class)->name('logs');

    Route::get('/user-settings', [SystemController::class, 'index_usersettings'])->name('user-settings');

    Route::get('/sites', ShowSites::class)->name('sites');
    Route::get('/buildings', ShowBuildings::class)->name('buildings');
    Route::get('/rooms', ShowRooms::class)->name('rooms');
});

Route::prefix('device')->middleware('auth:sanctum')->group(function () {
    Route::get('/backups', ShowBackups::class)->name('backups');
    Route::get('/uplinks', [DeviceUplinkController::class, 'index'])->name('uplinks');
    Route::get('/{device:id}', [DeviceController::class, 'show'])->name('show-device')->where('id', '[0-9]+');
    Route::get('/{device:id}/backups', ShowSwitchBackups::class)->name('device-backups')->where('id', '[0-9]+');
    Route::get('/{device:id}/ports/{port}', [DevicePortStatController::class, 'index'])->name('port-details-specific')->where('id', '[0-9]+');
});

Route::get('/device/backup/{id}/download/', [BackupController::class, 'downloadBackup'])->name('download-backup');

// Admin only routes
Route::middleware(['role.admin', 'auth:sanctum'])->group(function () {
    // Allgemeine Aktionen
    Route::get('/execute', [SSHController::class, 'index'])->name('perform-ssh');

    Route::post('/sites', [SiteController::class, 'store'])->name('create-site');
    Route::put('/sites', [SiteController::class, 'update'])->name('update-site');
    Route::delete('/sites', [SiteController::class, 'destroy'])->name('delete-site');
    Route::put('/sites/change', [SiteController::class, 'changeSite'])->name('change-site');

    Route::post('/buildings', [BuildingController::class, 'store'])->name('create-building');
    Route::put('/buildings', [BuildingController::class, 'update'])->name('update-building');
    Route::delete('/buildings', [BuildingController::class, 'destroy'])->name('delete-building');

    Route::post('/rooms', [RoomController::class, 'store']);
    Route::put('/rooms', [RoomController::class, 'update']);
    Route::delete('/rooms', [RoomController::class, 'destroy']);

    Route::post('/vlans', [VlanController::class, 'store']);
    Route::put('/vlans', [VlanController::class, 'update']);
    Route::delete('/vlans', [VlanController::class, 'destroy']);    

    Route::post('/router', [SystemController::class, 'storeRouter']);
    Route::put('/router', [SystemController::class, 'updateRouter']);
    Route::delete('/router', [SystemController::class, 'deleteRouter']);

    // Pubkey Aktionen
    Route::post('/pubkey/add', [PublicKeyController::class, 'store']);
    Route::delete('/pubkey/delete', [PublicKeyController::class, 'destroy']);

    Route::post('/privatekey/upload', function(Request $request) {
        $key = $request->input('key');
        return "<pre>".Crypt::encrypt($key)."</pre><br><b>Please create new file 'ssh.key' in storage/app/ and paste this encrypted key into it.</b>";
    });
    
    Route::get('/privatekey', function() {
        if(!Storage::disk('local')->get('ssh.key')) {
            return view('ssh.encrypt');
        } else {
            return response('SSH Key already exists', 400);
        }
    });

    // User Aktionen
    Route::put('/user/role', [SystemController::class, 'updateUserRole']);

    // MacType Aktionen
    Route::post('/clients/type', [MacTypeService::class, 'store']);
    Route::post('/clients/type/icon', [MacTypeService::class, 'storeIcon']);
    Route::delete('/clients/type', [MacTypeService::class, 'delete']);   
});

Route::prefix('device')->middleware(['role.admin', 'auth:sanctum'])->group(function () {
    // Views
    Route::put('/uplinks', [DeviceService::class, 'storeCustomUplinks']);

    // Aktionen für bestimmten device
    Route::post('/{device:id}/action/sync-pubkeys', [DeviceController::class, 'uploadPubkeysToSwitch'])->where('id', '[0-9]+');
    Route::post('/{device:id}/action/refresh', [DeviceService::class, 'refreshDevice'])->where('id', '[0-9]+');
    Route::post('/{device:id}/action/sync-vlans', [DeviceService::class, 'syncVlansToDevice'])->where('id', '[0-9]+');
    Route::post('/{device:id}/action/backup', [DeviceController::class, 'createBackup'])->where('id', '[0-9]+');
    Route::post('/{id}/action/update-untagged-ports', [DeviceController::class, 'setUntaggedVlanToPort'])->where('id', '[0-9]+');
    Route::post('/{id}/action/update-tagged-ports', [DeviceController::class, 'setTaggedVlanToPort'])->where('id', '[0-9]+');
    Route::post('/{id}/action/update-port-name', [DeviceController::class, 'setPortName'])->where('id', '[0-9]+');
    Route::post('/{id}/action/prepare-api', [DeviceService::class, 'startApiSession'])->where('id', '[0-9]+');

    // Backup Aktionen
    Route::post('/backup/restore', [DeviceController::class, 'restoreBackup']);
    Route::delete('/backup/delete', [BackupController::class, 'destroy']);

    // Switch Aktionen
    Route::post('/create', [DeviceController::class, 'store'])->name('create-switch');
    Route::put('/update', [DeviceController::class, 'update'])->name('update-switch');
    Route::delete('/delete', [DeviceController::class, 'destroy'])->name('delete-switch');

    // Aktionen für jeden Switch
    Route::post('/{id}/ssh/execute', [SSHController::class, 'performSSH'])->where('id', '[0-9]+');
    Route::post('/action/create-backup', [DeviceController::class, 'createBackupAllDevices']);
    Route::post('/action/sync-pubkeys', [DeviceController::class, 'uploadPubkeysAllDevices']);
    Route::post('/action/sync-vlans', [DeviceService::class, 'syncVlansToAllDevices'])->name('VLAN Sync');
});

// Login
Auth::routes();