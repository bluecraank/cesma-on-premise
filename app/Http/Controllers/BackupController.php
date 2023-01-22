<?php

namespace App\Http\Controllers;

use App\Devices\ArubaCX;
use App\Devices\ArubaOS;
use App\Http\Requests\StoreBackupRequest;
use App\Http\Requests\UpdateBackupRequest;
use App\Mail\SendBackupStatus;
use App\Models\Backup;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $backups = Backup::all()->keyBy('id');
        $devices = Device::all()->keyBy('id');


        foreach ($devices as $device) {
            $device->last_backup = $backups->where('device_id', $device->id)->last();
        }

        return view('switch.view_backups', compact('backups', 'devices'));
    }

    static function store($success, $data, $device)
    {

        if($success and $data and !is_array($data)) {
            $dataEncrypted = EncryptionController::encrypt($data);
        } else {
            $dataEncrypted = "No data received";
        }

        Backup::create([
            'device_id' => $device->id,
            'data' => $dataEncrypted,
            'status' => ($success) ? 1 : 0,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Backup  $backup
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $find = Backup::find($request->input('id'));
        if ($find->delete()) {
            LogController::log('Backup gelöscht', '{"id": "' . $request->id . '", "device_id": "' . $find->device_id . '", "created_at": "' . $find->created_at . '"}');
            return redirect()->back()->with('success', 'Backup deleted successfully');
        }

        return redirect()->back()->withErrors(['message' => 'Backup could not be deleted']);
    }

    static function getBackupsBySwitchId($id)
    {
        $backups = Backup::where('device_id', $id)->get()->sortByDesc('created_at')->keyBy('id');
        $device = Device::find($id);
        return view('switch.switch-backups', compact('backups', 'device'));
    }

    static function backupAll()
    {
        $time = microtime(true);
        Device::all()->each(function ($device) {
            switch ($device->type) {
                case 'aruba-os':
                    $start = microtime(true);
                    ArubaOS::createBackup($device);
                    $elapsed = microtime(true) - $start;
                    echo "Backup created: " . $device->name . " (" . $elapsed . "sec)\n";
                    break;
                case 'aruba-cx':
                    $start = microtime(true);
                    ArubaCX::createBackup($device);
                    $elapsed = microtime(true) - $start;
                    echo "Backup created: " . $device->name . " (" . $elapsed . "sec)\n";
                    break;
                default:
                    echo "Error: Unknown device type: " . $device->type . "\n";
            }
        });

        echo "Backups finished in " . number_format(microtime(true) - $time, 2) . " seconds\n";
    }

    static function downloadBackup($id)
    {
        $backup = Backup::find($id);
        $device = Device::find($backup->device_id);
        $filename = $device->name . '_' . $backup->created_at->format('Y-m-d_H-i-s') . '_BACKUP.txt';
        $data = EncryptionController::decrypt($backup->data);
        if($data == NULL) {
            $data = "Decrypting error (Wrong encryption key?)";
        }
        
        return response($data, 200)->header('Content-Type', 'text/plain')->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    static function sendMail()
    {

        $backups = Backup::all()->keyBy('id');
        $devices = Device::all()->keyBy('id');

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

            if ($modDevices[$key]->success_total == 0) {
                $totalError = false;
            }
        }

        Mail::to(config('app.backup_mail_address'))->send(new SendBackupStatus($backups, $modDevices, $totalError));

        Log::info('Success! Email has been sent successfully.');
    }
}
