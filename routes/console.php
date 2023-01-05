<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\EndpointController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('switch:refresh', function () {
    $this->comment(DeviceController::updateAllSwitches());
    $this->comment('Switches refreshed');
})->purpose('Refreshing all switches');

Artisan::command('switch:backup', function () {
    $this->comment(BackupController::getBackups());
    $this->comment('Switches backups finished');
})->purpose('Backup all switches');

Artisan::command('switch:backup:mail', function () {
    $this->comment(BackupController::sendMail());
    $this->comment('Backup mail sent');
})->purpose('Sent backup status');

Artisan::command('switch:macs:toClients', function () {
    $this->comment('--- START MERGING CLIENTS ---');
    $this->comment(EndpointController::storeMergedClientData());
    $this->comment('--- END MERGING CLIENTS ---');
})->purpose('Correlate MACs with Clients');