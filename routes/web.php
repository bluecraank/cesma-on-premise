<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\MacTypeController;
use App\Http\Controllers\PublicKeyController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SSHController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\VlanController;
use App\Livewire\DeviceExecuteSSH;
use App\Livewire\ShowBackups;
use App\Livewire\ShowBuildings;
use App\Livewire\ShowClients;
use App\Livewire\ShowDeviceBackups;
use App\Livewire\ShowDevices;
use App\Livewire\ShowLogs;
use App\Livewire\ShowReports;
use App\Livewire\ShowRooms;
use App\Livewire\ShowSites;
use App\Livewire\ShowUplinks;
use App\Livewire\ShowUsers;
use App\Livewire\ShowVlans;
use App\Livewire\SyncVlans;
use App\Models\Device;
use App\Models\Vlan;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Main Routes
Route::middleware(['auth:sanctum', 'check-first-admin'])->group(function () {
    Route::get('/', [SystemController::class,'dashboard'])->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Dashboard'), route('dashboard'));
    })->name('dashboard');

    Route::get('ssh', DeviceExecuteSSH::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Execute SSH'), route('ssh'));
    })->name('ssh');

    Route::get('settings/users', ShowUsers::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Users'), route('users'));
    })->name('users');

    Route::get('logs', ShowLogs::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Logs'), route('logs'));
    })->name('logs');

    Route::get('reports', ShowReports::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Reports'), route('reports'));
    })->name('reports');

    Route::get('topology', [SystemController::class, 'index_topology'])->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Topology'), route('topology'));
    })->name('topology');

    Route::get('clients', ShowClients::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Clients'), route('clients'));
    })->name('clients');

    Route::get('/sites', ShowSites::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Sites'), route('sites'));
    })->name('sites');

    Route::get('/rooms', ShowRooms::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Rooms'), route('rooms'));
    })->name('rooms');

});

Route::prefix('devices')->middleware(['auth:sanctum'])->group(function () {

    Route::get('/', ShowDevices::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Devices'), route('devices'));
    })->name('devices');

    Route::post('/{device}/execute', [SSHController::class, 'performSSH'])->name('perform-ssh');
    Route::post('/{device}/backup', [DeviceController::class, 'createBackup'])->name('create-backup');
    Route::post('/{device}/sync-pubkeys', [DeviceController::class, 'syncPubkeys'])->name('sync-pubkeys');
    Route::post('/{device}/sync-vlans', [DeviceController::class, 'syncVlansToDevice'])->name('sync-vlans');
    Route::post('/{device}/update', [DeviceService::class, 'refreshDevice'])->name('update-device');

    Route::get('/backups', ShowBackups::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Backups'), route('backups'));
    })->name('backups');

    Route::post('/backups', [DeviceController::class, 'createBackupAllDevices'])->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Backups'), route('backups'));
    })->name('create-backups');

    Route::get('/{device}/backups/{devicebackup}', [BackupController::class, 'downloadBackup'])->name('download-backup');


    Route::get('/backups/{backup}', ShowBackups::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Backups'), route('backups'));
    })->name('show-backup');


    Route::get('/{device}', [DeviceController::class, 'show'])->breadcrumbs(function (Trail $trail, Device $device) {
        $trail->parent('devices')
        ->push($device->name, route('show-device', $device->id));
    })->name('show-device');

    Route::get('/{device}/backups', ShowDeviceBackups::class)->breadcrumbs(function (Trail $trail, Device $device) {
        $trail->parent('devices')
        ->push($device->name, route('show-device', $device->id))
        ->push(__('Backups'), route('show-device-backups', $device->id));
    })->name('show-device-backups');


    Route::get('/{device}/ports/{port}', [DeviceController::class, 'showPort'])->breadcrumbs(function (Trail $trail, Device $device, $port) {
        $trail->parent('show-device', $device->id)
        ->push(__('Port'), route('show-port', $port));
    })->name('show-port');

});

Route::prefix('vlans')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', ShowVlans::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Vlans'), route('vlans'));
    })->name('vlans');

    Route::get('/sync', SyncVlans::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Sync vlans'), route('sync-vlans'));
    })->name('sync-vlans');

    Route::get('/{vlan}', [VlanController::class, 'index'])->breadcrumbs(function (Trail $trail, Vlan $vlan) {
        $trail->parent('vlans')
        ->push($vlan->name, route('show-vlan', $vlan->id));
    })->name('show-vlan');


    Route::post('/', [VlanController::class, 'store'])->name('create-vlan');
});

Route::prefix('sites')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', ShowSites::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Sites'), route('sites'));
    })->name('sites');

    Route::put('/', [SiteController::class, 'changeSite'])->name('change-site');
    Route::post('/', [SiteController::class, 'store'])->name('create-site');
});

Route::prefix('settings/snmp-gateways')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [SystemController::class, 'index_snmp'])->breadcrumbs(function (Trail $trail) {
        $trail->push(__('SNMP Gateways'), route('snmp'));
    })->name('snmp');

    Route::post('/', [SystemController::class, 'createGateway'])->name('create-gateway');
    Route::delete('/', [SystemController::class, 'deleteGateway'])->name('delete-gateway');
});

Route::prefix('settings/privatekey')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [SystemController::class, 'index_privatekey'])->breadcrumbs(function (Trail $trail) {
        $trail->push(__('SSH Privatekey'), route('privatekey'));
    })->name('privatekey');

    Route::post('/', [SSHController::class, 'store_privatekey'])->name('create-private-key');
});

Route::prefix('settings/publickeys')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [SystemController::class, 'index_publickeys'])->breadcrumbs(function (Trail $trail) {
        $trail->push(__('SSH Publickeys'), route('publickeys'));
    })->name('publickeys');

    Route::post('/', [PublicKeyController::class, 'store'])->name('create-public-key');
    Route::delete('/', [PublicKeyController::class, 'destroy'])->name('delete-public-key');
});


Route::prefix('settings/mac-types')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [SystemController::class, 'index_mac_type'])->breadcrumbs(function (Trail $trail) {
        $trail->push(__('MAC Prefix Icons'), route('mac-types'));
    })->name('mac-types');

    Route::post('/', [MacTypeController::class, 'store'])->name('create-mac-type');
    Route::put('/icon', [MacTypeController::class, 'update'])->name('update-mac-type-icon');
    Route::delete('/', [MacTypeController::class, 'destroy'])->name('delete-mac-type');
});


Route::prefix('buildings')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', ShowBuildings::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Buildings'), route('buildings'));
    })->name('buildings');

    Route::post('/', [BuildingController::class, 'store'])->name('create-building');
});

Route::prefix('rooms')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', ShowRooms::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Rooms'), route('rooms'));
    })->name('rooms');

    Route::post('/', [RoomController::class, 'store'])->name('create-room');
});

Route::get('/home', function() {
    return redirect()->route('dashboard');
});

Auth::routes();
