<?php

namespace App\Http\Controllers;

use App\Helper\CLog;
use App\Mail\SendBackupStatus;
use App\Models\Device;
use App\Models\DeviceBackup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class BackupController extends Controller
{
    static function store($success, $data, $restore, $device) {

        if ($success and $data and !is_array($data)) {
            $dataEncrypted = Crypt::encrypt($data);
            $resDataEncrypted = Crypt::encrypt($restore);
        } else {
            $dataEncrypted = "No data received";
            $resDataEncrypted = "No data received";
        }

        DeviceBackup::create([
            'device_id' => $device->id,
            'data' => $dataEncrypted,
            'restore_data' => $resDataEncrypted,
            'status' => ($success) ? 1 : 0,
        ]);
    }

    static function backupAll() {
        $time = microtime(true);
        Device::all()->each(function ($device) {
            $class = config('app.types')[$device->type] ?? null;

            if (!$class) {
                echo "Error: Unknown device type: " . $device->type . "\n";
                return;
            }

            if (!(config('app.backup_support')[$device->type] ?? false)) {
                echo "Skipped (backups not supported): " . $device->name . "\n";
                return;
            }

            $start = microtime(true);
            $success = $class::createBackup($device);
            $elapsed = microtime(true) - $start;
            echo ($success ? __('Backup successfully created') : __('Error creating backup')) . ": " . $device->name . " (" . $elapsed . "sec)\n";
        });

        echo "Backups finished in " . number_format(microtime(true) - $time, 2) . " seconds\n";
    }

    static function downloadBackup(Device $device, DeviceBackup $devicebackup) {

        if(!$devicebackup || !$device || $devicebackup->device_id != $device->id) {
            return abort(404);
        }

        $filename = $device->name . '_' . $devicebackup->created_at->format('Y-m-d_H-i-s') . '_BACKUP.txt';
        $data = $devicebackup->decryptedData();

        CLog::info("Backup", __('Backup :id downloaded', ['id' => $devicebackup->id]), null, __('Device: :name, Backup created: :date', ['name' => $devicebackup->device->name, 'date' => $devicebackup->created_at]));


        return response($data, 200)->header('Content-Type', 'text/plain')->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    static function sendMail() {
        if (empty(config('app.backup_mail_address'))) {
            CLog::error("Backup", 'Backup status mail not sent, BACKUP_MAIL_ADDRESS is not set');
            return;
        }

        // Do not load the (large) backup contents
        $backups = DeviceBackup::get(['id', 'device_id', 'status', 'created_at']);
        $devices = Device::with('site')->get()->keyBy('id')->sortBy('name');

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $modDevices = [];
        // true = every device with backup support has been backed up successfully this week
        $totalError = true;

        foreach ($devices as $key => $device) {
            $deviceBackups = $backups->where('device_id', $device->id);
            $weekBackups = $deviceBackups->where('created_at', '>=', $startOfWeek)->where('created_at', '<=', $endOfWeek);

            $modDevices[$key] = new \stdClass();
            $modDevices[$key]->name = $device->name;
            $modDevices[$key]->backups = $deviceBackups->count();
            $modDevices[$key]->last_backup = $deviceBackups->where('status', 1)->sortBy('created_at')->last();
            $modDevices[$key]->success = $weekBackups->where('status', 1)->count();
            $modDevices[$key]->fail = $weekBackups->where('status', 0)->count();
            $modDevices[$key]->supported = config('app.backup_support')[$device->type] ?? false;
            // Only successful if there actually was a successful backup and none failed
            $modDevices[$key]->success_total = ($modDevices[$key]->success > 0 && $modDevices[$key]->fail == 0) ? 1 : 0;
            $modDevices[$key]->site = $device->site?->name;

            if ($modDevices[$key]->supported && $modDevices[$key]->success_total == 0) {
                $totalError = false;
            }
        }

        Mail::to(config('app.backup_mail_address'))->send(new SendBackupStatus($modDevices, $totalError));

        CLog::info("Backup", 'Backup status mail has been sent');
    }
}
