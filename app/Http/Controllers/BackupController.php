<?php

namespace App\Http\Controllers;

use App\Devices\ArubaCX;
use App\Devices\ArubaOS;
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
            switch ($device->type) {
                case 'aruba-os':
                    $start = microtime(true);
                    ArubaOS::createBackup($device);
                    $elapsed = microtime(true) - $start;
                    echo __('Backup successfully created').": " . $device->name . " (" . $elapsed . "sec)\n";
                    break;
                case 'aruba-cx':
                    $start = microtime(true);
                    ArubaCX::createBackup($device);
                    $elapsed = microtime(true) - $start;
                    echo __('Backup successfully created').": " . $device->name . " (" . $elapsed . "sec)\n";
                    break;
                default:
                    echo "Error: Unknown device type: " . $device->type . "\n";
            }
        });

        echo "Backups finished in " . number_format(microtime(true) - $time, 2) . " seconds\n";
    }

    static function downloadBackup(Device $device, DeviceBackup $devicebackup) {

        if(!$devicebackup || !$device) {
            return abort(404);
        }

        $filename = $device->name . '_' . $devicebackup->created_at->format('Y-m-d_H-i-s') . '_BACKUP.txt';
        $data = Crypt::decrypt($devicebackup->data);

        if ($data == NULL) {
            $data = "Decrypting error (Wrong encryption key?)";
        }

        CLog::info("Backup", __('Backup :id downloaded', ['id' => $devicebackup->id]), null, __('Device: :name, Backup created: :date', ['name' => $devicebackup->device->name, 'date' => $devicebackup->created_at]));


        return response($data, 200)->header('Content-Type', 'text/plain')->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    static function sendMail() {

        $backups = DeviceBackup::all()->keyBy('id');
        $devices = Device::all()->keyBy('id')->sortBy('name');

        $modDevices = [];
        $totalError = true;

        foreach ($devices as $key => $device) {
            $modDevices[$key] = new \stdClass();
            $modDevices[$key]->name = $device->name;
            $modDevices[$key]->backups = $backups->where('device_id', $device->id)->count();
            $modDevices[$key]->last_backup = $backups->where('device_id', $device->id)->where('status', 1)->last();
            $modDevices[$key]->success = $backups->where('device_id', $device->id)->where('status', 1)->where('created_at', '>', Carbon::now()->startOfWeek())->where('created_at', '<', Carbon::now()->endOfWeek())->count();
            $modDevices[$key]->fail = $backups->where('device_id', $device->id)->where('status', 0)->where('created_at', '>', Carbon::now()->startOfWeek())->where('created_at', '<', Carbon::now()->endOfWeek())->count();
            $modDevices[$key]->success_total = ($modDevices[$key]->fail == 0) ? 1 : 0;
            $modDevices[$key]->site = $device->site->name;

            if ($modDevices[$key]->success_total == 0) {
                $totalError = false;
            }
        }

        Mail::to(config('app.backup_mail_address'))->send(new SendBackupStatus($modDevices, $totalError));

        CLog::info("Backup", 'Backup status mail has been sent');
    }
}
