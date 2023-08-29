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
use App\Livewire\ShowRooms;
use App\Livewire\ShowSites;
use App\Livewire\ShowUplinks;
use App\Livewire\ShowUsers;
use App\Livewire\ShowVlans;
use App\Livewire\SyncVlans;
use App\Models\Device;
use App\Models\DevicePort;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Breadcrumbs;
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

    Route::get('/uplinks', ShowUplinks::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Uplinks'), route('uplinks'));
    })->name('uplinks');

});

Route::prefix('devices')->middleware(['auth:sanctum'])->group(function () {

    Route::get('/', ShowDevices::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Devices'), route('devices'));
    })->name('devices');

    Route::post('/{device}/execute', [SSHController::class, 'performSSH'])->name('perform-ssh');
    Route::post('/{device}/backup', [DeviceController::class, 'createBackup'])->name('create-backup');
    Route::post('/{device}/sync-pubkeys', [DeviceController::class, 'syncPubkeys'])->name('sync-pubkeys');
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

    Route::get('/{id}', function () {
        return view('dashboard');
    })->breadcrumbs(function (Trail $trail) {
        $trail->parent('vlans')
        ->push(__('Vlan'), route('show-vlan', 1));
    })->name('show-vlan');


    Route::post('/', [VlanController::class, 'store'])->name('create-vlan');
});

Route::prefix('sites')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', ShowSites::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Sites'), route('sites'));
    })->name('sites');

    Route::get('/{id}', function () {
        return view('dashboard');
    })->breadcrumbs(function (Trail $trail) {
        $trail->parent('sites')
        ->push(__('Site'), route('show-site', 1));
    })->name('show-site');

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

    Route::get('/{id}', function () {
        return view('dashboard');
    })->breadcrumbs(function (Trail $trail) {
        $trail->parent('buildings')
            ->push(__('Building'), route('show-building', 1));
    })->name('show-building');

    Route::post('/', [BuildingController::class, 'store'])->name('create-building');
});

Route::prefix('rooms')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', ShowRooms::class)->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Rooms'), route('rooms'));
    })->name('rooms');

    Route::get('/{id}', function () {
        return view('dashboard');
    })->breadcrumbs(function (Trail $trail) {
        $trail->parent('rooms')
            ->push(__('Room'), route('show-room', 1));
    })->name('show-room');

    Route::post('/', [RoomController::class, 'store'])->name('create-room');
});

Route::get('/test', function () {
    $ip = "10.50.2.120";

    $snmp = snmp2_real_walk($ip, "public", ".1.3.6.1.2.1.17.7.1.2.2.1.2", 500000, 2);


    if($snmp == false || $snmp == [] || $snmp == "\"\"") {
        return false;
    }

    $data = [];
    foreach($snmp as $key => $value) {
        $cur_key = $key;
        $cur_value = $value;
        $mac = explode(".", $key);
        $decimal_mac = [
            $mac[count($mac) - 6],
            $mac[count($mac) - 5],
            $mac[count($mac) - 4],
            $mac[count($mac) - 3],
            $mac[count($mac) - 2],
            $mac[count($mac) - 1],
        ];

        foreach($decimal_mac as $key => $value) {
            $new_value = $value;

            $new_value = dechex($new_value);

            if(strlen($new_value) == 1) {
                $new_value = "0" . $new_value;
            }

            $decimal_mac[$key] = $new_value;
        }

        $decimal_mac = implode(":", $decimal_mac);
        $data[$decimal_mac] = [];
        $datavalue = str_replace("INTEGER: ", "", $cur_value);
        $data[$decimal_mac] = $datavalue ;
    }

    $ports = DevicePort::where('device_id', 13)->get()->keyBy('snmp_if_index')->toArray();

    $new_data = [];
    foreach($data as $mac => $port) {
        echo $port . "\n";
        if(isset($ports[$port])) {
            $new_data[$mac] = $ports[$port]['name'];
        }
    }

    dd($new_data);
})->name('test');
Auth::routes();
